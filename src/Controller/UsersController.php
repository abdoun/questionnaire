<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
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
        if($this->requestStack->getSession()->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        return $this->render('users/index.html.twig', [
            'users' => $usersRepository->findAll(),
            'title'=>'Userarea',
            'head' => 'Userarea hallo: '.$this->requestStack->getSession()->get('username')
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
    public function new(Request $request): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(md5($user->getPassword()));
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
            $userCount = $userRepo->count([
                'username'=>$request->get('username'),
                'password'=>md5($request->get('password')),
                'active'=>1
            ]);
            if(!$userCount) {
                $msg = 'Sorry, there is no user with this username and password';
            } else {
                $msg = 'thanks';
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
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
            'title'=>'Login',
            'head' => 'Login',
        ]);
    }
    

    #[Route("/{id}/edit", name: "users_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Users $user): Response
    {
        $user->setPassword('');
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($user->getPassword() !== null) {
                $user->setPassword(md5($user->getPassword()));
            }            
            $this->doctrine->getManager()->flush();

            return $this->redirectToRoute('users_index');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'title'=>'Edit',
            'head' => 'Edit user',
        ]);
    }

    #[Route("/{id}", name: "users_delete", methods: ["DELETE"])]
    public function delete(Request $request, Users $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('users_index');
    }

    
}
