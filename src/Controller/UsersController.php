<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/users")
 */
class UsersController extends AbstractController
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="users_index", methods={"GET"})
     */
    public function index(UsersRepository $usersRepository)
    
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        return $this->render('users/index.html.twig', [
            'users' => $usersRepository->findAll(),
            'title'=>'Userarea',
            'head' => 'Userarea hallo: '.$this->session->get('username')
        ]);
    }
    /**
     * @Route("/dashboard", name="users_dashboard", methods={"GET"})
     */
    public function dashboard(): Response
    {
         if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        } 
        return $this->render('users/dashboard.html.twig', [
            'title'=>'Userarea',
            'head' => 'Userarea hallo: '.$this->session->get('username')
        ]);
    }

    /**
     * @Route("/new", name="users_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(md5($user->getPassword()));
            $entityManager = $this->getDoctrine()->getManager();
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
    /**
     * @Route("/login", name="users_login", methods={"GET"})
     */
    public function login(): Response
    {
        if($this->session->get('username') !== null) {
            return $this->redirectToRoute('users_dashboard');
        }
        return $this->render('users/login.html.twig', [
            'title'=>'Login',
            'head' => 'Login',
            'msg'=>''
        ]);
    }
    /**
     * @Route("/signout", name="users_signout")
     */
    public function signout(): Response
    {
        $this->session->remove('username');
        return $this->redirectToRoute('users_login');
    }

    /**
     * @Route("/signin", name="users_signin")
     */
    public function signin(Request $request, UsersRepository $userRepo): Response
    {
        if($this->session->get('username') !== null) {
            return $this->redirectToRoute('users_index');
        }

        if($request->get('username') == '' || $request->get('password') == '') {
            $msg='Username or Password is empty';
            
        } elseif($this->session->get('new_string') != $request->get('code_capthca')) {
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
                $this->session->set('username',$request->get('username'));
                return $this->redirectToRoute('users_dashboard');
            }
            
        }

        return $this->render('users/login.html.twig', [
            'title'=>'Login',
            'head' => 'Login',
            'msg'=> $msg
        ]);
    }

/**
     * @Route("/imccaptcha/{rand}", name="imccaptcha")
     */
    public function imgcaptcha(int $rand=1): Response
	{
		srand((double)microtime()*1000000000); 
		$string = md5(rand(0,999999));
        $this->session->set("new_string",strtoupper( substr($string, 17, 4)));
        $im = imagecreatetruecolor(160, 80);
        $white = ImageColorAllocate($im, 255, 255, 255); 
        $grey = ImageColorAllocate($im, 38, 90, 136);
        $black = imagecolorallocate($im, 0, 0, 0);

        ImageFill($im, 0, 0, $grey);
        //ImageString($im, 7, 80, 10, $_SESSION[new_string], $white);
        imagettftext($im,35,10,25,70,$black,'fonts/verdana.ttf',$this->session->get('new_string'));
        imagettftext($im,35,10,20,65,$white,'fonts/verdana.ttf',$this->session->get('new_string'));

		return new Response(
            header('Content-type: image/png').imagepng($im).imagedestroy($im)
        );
	}
    

    /**
     * @Route("/{id}", name="users_show", methods={"GET"})
     */
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
            'title'=>'Login',
            'head' => 'Login',
        ]);
    }
    

    /**
     * @Route("/{id}/edit", name="users_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Users $user): Response
    {
        $user->setPassword('');
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($user->getPassword() !== null) {
                $user->setPassword(md5($user->getPassword()));
            }            
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('users_index');
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'title'=>'Edit',
            'head' => 'Edit user',
        ]);
    }

    /**
     * @Route("/{id}", name="users_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Users $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('users_index');
    }

    
}
