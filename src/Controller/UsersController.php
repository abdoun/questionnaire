<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Form\SignupType;
use App\Repository\UsersRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route("/users")]
class UsersController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private ManagerRegistry $doctrine)
    {
    }

    #[Route("/", name: "users_index", methods: ["GET"])]
    public function index(UsersRepository $usersRepository)
    {
        $currentUser = $this->getCurrentUser($usersRepository);
        if ($currentUser === null) {
            return $this->redirectToRoute('users_login');
        }

        $isAdmin = $this->isAdmin($currentUser);

        return $this->render('users/index.html.twig', [
            'users' => $isAdmin ? $usersRepository->findAll() : [$currentUser],
            'isAdmin' => $isAdmin,
            'title'=>'Userarea',
            'head' => 'Userarea hallo: '.$currentUser->getUsername()
        ]);
    }
    #[Route("/dashboard", name: "users_dashboard", methods: ["GET"])]
    public function dashboard(): Response
    {
         if($this->requestStack->getSession()->get('username') === null) {
            return $this->redirectToRoute('users_login');
        } 
        return $this->render('users/dashboard.html.twig', [
            'title'=>'Userarea',
            'head' => 'Userarea hallo: '.$this->requestStack->getSession()->get('username')
        ]);
    }

    #[Route("/new", name: "users_new", methods: ["GET","POST"])]
    public function new(Request $request, UsersRepository $usersRepository): Response
    {
        $currentUser = $this->getCurrentUser($usersRepository);
        if ($currentUser === null) {
            return $this->redirectToRoute('users_login');
        }
        if (!$this->isAdmin($currentUser)) {
            throw $this->createAccessDeniedException();
        }

        $user = new Users();
        $form = $this->createForm(UsersType::class, $user, ['is_admin' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(password_hash((string) $user->getPassword(), PASSWORD_DEFAULT));
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('users_index');
        }

        return $this->render('users/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'title'=>'New',
            'head' => 'Add a user',
        ]);
    }
    #[Route("/login", name: "users_login", methods: ["GET"])]
    public function login(): Response
    {
        if($this->requestStack->getSession()->get('username') !== null) {
            return $this->redirectToRoute('users_dashboard');
        }
        return $this->render('users/login.html.twig', [
            'title'=>'Login',
            'head' => 'Login',
            'msg'=>''
        ]);
    }

    #[Route("/signup", name: "users_signup", methods: ["GET", "POST"])]
    public function signup(Request $request, UsersRepository $userRepo): Response
    {
        if ($this->requestStack->getSession()->get('username') !== null) {
            return $this->redirectToRoute('users_dashboard');
        }

        $user = new Users();
        $form = $this->createForm(SignupType::class, $user);
        $form->handleRequest($request);
        $msg = '';
        $formErrors = [];

        if ($form->isSubmitted()) {
            $captcha = strtoupper(trim((string) $request->get('code_captcha')));
            $sessionCaptcha = (string) $this->requestStack->getSession()->get('new_string');

            if ($sessionCaptcha === '' || $captcha !== $sessionCaptcha) {
                $msg = 'Code is wrong';
            } elseif (!$form->isValid()) {
                foreach ($form->getErrors(true, true) as $error) {
                    $formErrors[] = $error->getMessage();
                }

                if ($formErrors === []) {
                    $msg = 'Please correct the highlighted fields and try again.';
                }
            } else {
                $username = trim((string) $user->getUsername());
                $email = trim((string) $user->getEmail());

                if ($userRepo->findOneBy(['username' => $username])) {
                    $msg = 'This username is already taken';
                } elseif ($userRepo->findOneBy(['email' => $email])) {
                    $msg = 'This email address is already registered';
                } else {
                    $user->setUsername($username);
                    $user->setEmail($email);
                    $user->setPassword(password_hash((string) $user->getPassword(), PASSWORD_DEFAULT));
                    $user->setActive(true);
                    $user->setLevel('user');

                    $entityManager = $this->doctrine->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->requestStack->getSession()->set('username', $user->getUsername());
                    $this->requestStack->getSession()->remove('new_string');

                    return $this->redirectToRoute('users_dashboard');
                }
            }
        }

        return $this->render('users/signup.html.twig', [
            'form' => $form->createView(),
            'title' => 'Sign up',
            'head' => 'Create an account',
            'msg' => $msg,
            'formErrors' => $formErrors,
        ]);
    }
    #[Route("/signout", name: "users_signout")]
    public function signout(): Response
    {
        $this->requestStack->getSession()->remove('username');
        return $this->redirectToRoute('users_login');
    }

    #[Route("/signin", name: "users_signin")]
    public function signin(Request $request, UsersRepository $userRepo): Response
    {
        if($this->requestStack->getSession()->get('username') !== null) {
            return $this->redirectToRoute('users_index');
        }

        if($request->get('username') == '' || $request->get('password') == '') {
            $msg='Username or Password is empty';
            
        } elseif($this->requestStack->getSession()->get('new_string') != $request->get('code_capthca')) {
            $msg = 'Code is wrong';
        } else {
            $user = $userRepo->findOneBy([
                'username' => $request->get('username'),
                'active' => 1
            ]);
            $password = (string) $request->get('password');
            $passwordValid = $user !== null && (
                password_verify($password, (string) $user->getPassword())
                || hash_equals((string) $user->getPassword(), md5($password))
            );

            if (!$passwordValid) {
                $msg = 'Sorry, there is no user with this username and password';
            } else {
                $msg = 'thanks';
                if (password_needs_rehash((string) $user->getPassword(), PASSWORD_DEFAULT)) {
                    $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
                    $this->doctrine->getManager()->flush();
                }
                $this->requestStack->getSession()->set('username',$request->get('username'));
                return $this->redirectToRoute('users_dashboard');
            }
            
        }

        return $this->render('users/login.html.twig', [
            'title'=>'Login',
            'head' => 'Login',
            'msg'=> $msg
        ]);
    }

#[Route("/imccaptcha/{rand}", name: "imccaptcha")]
    public function imgcaptcha(int $rand=1): Response
	{
        $string = md5(random_int(0, 999999));
        $captcha = strtoupper(substr($string, 17, 4));
        $this->requestStack->getSession()->set("new_string", $captcha);
        $image = imagecreatetruecolor(160, 80);
        $white = imagecolorallocate($image, 255, 255, 255);
        $grey = imagecolorallocate($image, 38, 90, 136);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $grey);
        $fontPath = $this->getParameter('kernel.project_dir') . '/public/fonts/verdana.ttf';
        imagettftext($image, 35, 10, 25, 70, $black, $fontPath, $captcha);
        imagettftext($image, 35, 10, 20, 65, $white, $fontPath, $captcha);

        ob_start();
        try {
            if (!imagepng($image)) {
                throw new \RuntimeException('Failed to generate CAPTCHA image.');
            }
            $imageData = ob_get_contents();
            if ($imageData === false) {
                throw new \RuntimeException('Failed to read generated CAPTCHA image.');
            }
        } finally {
            ob_end_clean();
            imagedestroy($image);
        }

        return new Response($imageData, Response::HTTP_OK, ['Content-Type' => 'image/png']);
	}
    

    #[Route("/{id}", name: "users_show", methods: ["GET"])]
    public function show(Users $user, UsersRepository $usersRepository): Response
    {
        $currentUser = $this->getCurrentUser($usersRepository);
        if ($currentUser === null) {
            return $this->redirectToRoute('users_login');
        }
        $this->denyUnlessCanAccessUser($currentUser, $user);

        return $this->render('users/show.html.twig', [
            'user' => $user,
            'isAdmin' => $this->isAdmin($currentUser),
            'title'=>'Login',
            'head' => 'Login',
        ]);
    }
    

    #[Route("/{id}/edit", name: "users_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Users $user, UsersRepository $usersRepository): Response
    {
        $currentUser = $this->getCurrentUser($usersRepository);
        if ($currentUser === null) {
            return $this->redirectToRoute('users_login');
        }
        $this->denyUnlessCanAccessUser($currentUser, $user);

        $originalPassword = $user->getPassword();
        $user->setPassword('');
        $isAdmin = $this->isAdmin($currentUser);
        $form = $this->createForm(UsersType::class, $user, [
            'is_admin' => $isAdmin,
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = (string) $user->getPassword();
            $user->setPassword($password === ''
                ? (string) $originalPassword
                : password_hash($password, PASSWORD_DEFAULT));
            $this->doctrine->getManager()->flush();

            if ($currentUser->getId() === $user->getId()) {
                $this->requestStack->getSession()->set('username', $user->getUsername());
            }

            return $this->redirectToRoute('users_index');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'isAdmin' => $isAdmin,
            'title'=>'Edit',
            'head' => 'Edit user',
        ]);
    }

    #[Route("/{id}", name: "users_delete", methods: ["DELETE"])]
    public function delete(Request $request, Users $user, UsersRepository $usersRepository): Response
    {
        $currentUser = $this->getCurrentUser($usersRepository);
        if ($currentUser === null) {
            return $this->redirectToRoute('users_login');
        }
        if (!$this->isAdmin($currentUser)) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('users_index');
    }

    private function getCurrentUser(UsersRepository $usersRepository): ?Users
    {
        $username = $this->requestStack->getSession()->get('username');
        if (!is_string($username) || $username === '') {
            return null;
        }

        $user = $usersRepository->findOneBy(['username' => $username, 'active' => true]);
        if ($user === null) {
            $this->requestStack->getSession()->remove('username');
        }

        return $user;
    }

    private function isAdmin(Users $user): bool
    {
        return $user->getLevel() === 'admin';
    }

    private function denyUnlessCanAccessUser(Users $currentUser, Users $targetUser): void
    {
        if (!$this->isAdmin($currentUser) && $currentUser->getId() !== $targetUser->getId()) {
            throw $this->createAccessDeniedException();
        }
    }
}
