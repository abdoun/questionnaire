<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Questions;
use App\Entity\Suggestions;

/**
 * @Route("/suggestions")
 */
class SuggestionsController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

/**
     * @Route("/new", name="suggestions_new", methods={"GET"})
     */
    public function new(Request $request): Response
    {
        $msg = '';
        $msg_class = 'light';
        $suggestion = new Suggestions();
        if($request->get('id')!='') {
            $repository = $this->getDoctrine()->getRepository(Suggestions::class);
            $suggestion = $repository->findOneBy(['id'=>$request->get('id')]);
        }

        return $this->render('suggestions/new.html.twig', [            
            'suggestion'=>$suggestion,
            'msg' => $msg,
            'msg_class' => $msg_class,
            'questionId' => $request->get('questionId')
        ]);
        
    }

    /**
     * @Route("/update", name="suggestions_update", methods={"GET","POST"})
     */
    public function update(Request $request): Response
    {
        $msg = '';
        $msg_class = 'light';        
        $suggestion = new Suggestions();
        if($request->get('answer')=='') {
            $msg = 'Sorry, you have not filled the required fields!';
            $msg_class = 'danger';
        } else {
            //$suggestion = new Suggestions();
            $entityManager = $this->getDoctrine()->getManager();
            $msg = 'the step have been just added';

            if($request->get('id')!='') {
                $suggestion = $entityManager->getRepository(Suggestions::class)->find($request->get('id'));
                $msg = 'the Question have been just updated';
            }
            $suggestion->setAnswer($request->get('answer'));
            $suggestion->setActive(($request->get('active') ? true : false));
            $suggestion->setQuestion($this->getQuestion($request->get('questionId')));


            $entityManager->persist($suggestion);
            $entityManager->flush();

            
            $msg_class = 'success';
        }
        return $this->render('suggestions/new.html.twig', [            
            'suggestion'=>$suggestion,
            'msg' => $msg,
            'msg_class' => $msg_class,
            'questionId' => $request->get('questionId')
        ]);
    }

     /**
     * @Route("/delete/{id}", name="suggestions_delete", methods={"GET"})
     * 
     */
    public function delete($id): Response
    {
        if($id!='') {
            $suggestion = new Suggestions();
            $entityManager = $this->getDoctrine()->getManager();
            $suggestion = $entityManager->getRepository(Suggestions::class)->find($id);
            
            $entityManager->remove($suggestion);
            $entityManager->flush();
        }           
        return new Response(null, 204);
    }

    /**
     * @Route("/{id}", name="suggestions", methods={"GET"})
     */
    public function index(Questions $question)
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        $repository = $this->getDoctrine()->getRepository(Suggestions::class);
        $suggestions = $repository->findBy(['question'=>$question->getId()],array('id' => 'ASC'));

        return $this->render('suggestions/index.html.twig', [
            'controller_name' => 'SuggestionsController',
            'title' => 'Questionnaires',
            'head' => 'Userarea hallo: '.$this->session->get('username'),
            'question' => $question,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * return Question
     */
    public function getQuestion($id): Questions
    {
        $repository = $this->getDoctrine()->getRepository(Questions::class);
        $qeustion= $repository->findOneBy(['id'=>$id]);
        return $qeustion;
    }
}
