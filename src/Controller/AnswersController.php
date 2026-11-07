<?php

namespace App\Controller;

use App\Entity\Answers;
use App\Entity\Questions;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route("/answers")]
class AnswersController extends AbstractController
{
    public function __construct(private RequestStack $requestStack, private ManagerRegistry $doctrine)
    {
    }

    #[Route("/", name: "answers")]
    public function index()
    {
        if($this->requestStack->getSession()->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        return $this->render('answers/index.html.twig', [
            'controller_name' => 'AnswersController',
        ]);
    }
    #[Route("/show/{id}", name: "answer_show")]
    public function show(Questions $question): Response
    {
        if($this->requestStack->getSession()->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        $repository = $this->doctrine->getRepository(Answers::class);
        $answers = $repository->findBy(['question'=>$question->getId()]);

        return $this->render('answers/show.html.twig', [
            'answers' => $answers,
        ]);
    }
}
