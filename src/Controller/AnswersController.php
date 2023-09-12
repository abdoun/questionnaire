<?php

namespace App\Controller;

use App\Entity\Answers;
use App\Entity\Questions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
     * @Route("/answers")
     */
class AnswersController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="answers")
     */
    public function index()
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        return $this->render('answers/index.html.twig', [
            'controller_name' => 'AnswersController',
        ]);
    }
    /**
     * @Route("/show/{id}", name="answer_show")
     */
    public function show(Questions $question): Response
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        $repository = $this->getDoctrine()->getRepository(Answers::class);
        $answers = $repository->findBy(['question'=>$question->getId()]);

        return $this->render('answers/show.html.twig', [
            'answers' => $answers,
        ]);
    }
}
