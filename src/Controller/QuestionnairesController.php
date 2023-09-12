<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Users;
use App\Entity\Questionnaires;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/questionnaires")
 */
class QuestionnairesController extends AbstractController
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    /**
     * @Route("/", name="questionnaires")
     */
    public function index(): Response
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        $repository = $this->getDoctrine()->getRepository(Questionnaires::class);
        $questionnaires = $repository->findBy(['user'=>$this->getUser()]);
        
        return $this->render('questionnaires/index.html.twig', [
            'controller_name' => 'QuestionnairesController',
            'title' => 'Questionnaires',
            'head' => 'Userarea hallo: '.$this->session->get('username'),
            'questionnaires'=>$questionnaires
        ]);
    }

    /**
     * @Route("/browse", name="questionnaires_browse")
     */
    public function browse(): Response
    {

        $repository = $this->getDoctrine()->getRepository(Questionnaires::class);
        $questionnaires = $repository->findBy(['active'=>1]);
        
        return $this->render('questionnaires/browse.html.twig', [
            'title' => 'Browse all active Questionnaires',
            'questionnaires'=>$questionnaires
        ]);
    }

    /**
     * @Route("/new", name="questionnaires_new", methods={"GET"})
     */
    public function new(Request $request): Response
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        $msg = '';
        $msg_class = 'light';
        $questionnaire = new Questionnaires();
        if($request->get('id')!='') {
            $repository = $this->getDoctrine()->getRepository(Questionnaires::class);
            $questionnaire = $repository->findOneBy(['id'=>$request->get('id')]);
        }
        return $this->render('questionnaires/new.html.twig', [            
            'questionnaire'=>$questionnaire,
            'msg' => $msg,
            'msg_class' => $msg_class,
        ]);
    }

    /**
     * @Route("/update", name="questionnaires_update", methods={"GET","POST"})
     */
    public function update(Request $request): Response
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        //var_dump($request->request->all());//exit;
        $msg = '';
        $msg_class = 'light';
        
        $questionnaire = new Questionnaires();
        if($request->get('name')=='' || $request->get('publish_date')=='') {
            $msg = 'Sorry, you have not filled the required fields!';
            $msg_class = 'danger';
        } else {
            
            $entityManager = $this->getDoctrine()->getManager();
            $msg = 'the Questionnaire have been just added';

            if($request->get('id')!='') {
                $questionnaire = $entityManager->getRepository(Questionnaires::class)->find($request->get('id'));
                $msg = 'the Questionnaire have been just updated';
            }

            $questionnaire->setName($request->get('name'));
            $questionnaire->setPublishDate(new \DateTime('@'.strtotime($request->get('publish_date'))));
            $questionnaire->setLanguage($request->get('language'));
            $questionnaire->setActive(($request->get('active') ? true : false));
            $questionnaire->setUser($this->getUserObj());
            $questionnaire->setDescription($request->get('description'));
            $questionnaire->setDetails($request->get('details'));

            $entityManager->persist($questionnaire);
            $entityManager->flush();

            
            $msg_class = 'success';
            
        }
        

        return $this->render('questionnaires/new.html.twig', [            
            'questionnaire'=>$questionnaire,
            'msg' => $msg,
            'msg_class' => $msg_class
        ]);
    }
    
    /**
     * @Route("/delete", name="questionnaires_delete", methods={"GET"})
     */
    public function delete(Request $request): Response
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        if($request->get('id')!='') {
            $questionnaire = new Questionnaires();
            $entityManager = $this->getDoctrine()->getManager();
            $questionnaire = $entityManager->getRepository(Questionnaires::class)->find($request->get('id'));
            $entityManager->remove($questionnaire);
            $entityManager->flush();
        }           
        return new Response(null, 204);
    }
    /**
     * return int
     */
    public function getUser()
    {
        $repository = $this->getDoctrine()->getRepository(Users::class);
        $user= $repository->findOneBy(['username'=>$this->session->get('username')]);
        return $user->getId();
    }

    /**
     * return obj
     */
    public function getUserObj()
    {
        $repository = $this->getDoctrine()->getRepository(Users::class);
        $user= $repository->findOneBy(['username'=>$this->session->get('username')]);
        return $user;
    }
}
