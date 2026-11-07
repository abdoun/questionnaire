<?php

namespace App\Controller;

use App\Entity\Answers;
use App\Entity\Questions;
use App\Entity\Steps;
use App\Entity\Suggestions;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/questions")]
class QuestionsController extends AbstractController
{

    public function __construct(private RequestStack $requestStack, private ManagerRegistry $doctrine)
    {
    }
    

    #[Route("/new", name: "questions_new", methods: ["GET"])]
    public function new(Request $request): Response
    {
        $msg = '';
        $msg_class = 'light';
        $question = new Questions();
        if($request->get('id')!='') {
            $repository = $this->doctrine->getRepository(Questions::class);
            $question = $repository->findOneBy(['id'=>$request->get('id')]);
        }

        return $this->render('questions/new.html.twig', [            
            'question'=>$question,
            'msg' => $msg,
            'msg_class' => $msg_class,
            'stepId' => $request->get('stepId')
        ]);
        
    }

    #[Route("/update", name: "questions_update", methods: ["GET","POST"])]
    public function update(Request $request): Response
    {
        $msg = '';
        $msg_class = 'light';        
        $question = new Questions();
        if($request->get('question_text')=='') {
            $msg = 'Sorry, you have not filled the required fields!';
            $msg_class = 'danger';
        } else {
           
            $entityManager = $this->doctrine->getManager();
            $msg = 'the step have been just added';

            if($request->get('id')!='') {
                $question = $entityManager->getRepository(Questions::class)->find($request->get('id'));
                $msg = 'the Question have been just updated';
            }
            $question->setQuestionType($request->get('question_type'));
            $question->setActive(($request->get('active') ? true : false));
            $question->setStep($this->getStep($request->get('stepId')));
            $question->setQuestionText($request->get('question_text'));

            $entityManager->persist($question);
            $entityManager->flush();

            
            $msg_class = 'success';
        }
        return $this->render('questions/new.html.twig', [            
            'question'=>$question,
            'msg' => $msg,
            'msg_class' => $msg_class,
            'stepId' => $request->get('stepId')
        ]);
    }

     #[Route("/delete/{id}", name: "questions_delete", methods: ["GET"])]
    public function delete($id): Response
    {
        if($id!='') {
            $question = new Questions();
            $entityManager = $this->doctrine->getManager();
            $question = $entityManager->getRepository(Questions::class)->find($id);
            
            $entityManager->remove($question);
            $entityManager->flush();
        }           
        return new Response(null, 204);
    }
    #[Route("/{id}", name: "questions", methods: ["GET"])]
    public function index(Steps $step)
    {

        if($this->requestStack->getSession()->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        $repository = $this->doctrine->getRepository(Questions::class);
        $questions = $repository->findBy(['step'=>$step->getId()],array('id' => 'ASC'));
        $answerRepository = $this->doctrine->getRepository(Answers::class);
        $answerCount = [];
        $answerSuggesionCount = [];
        foreach($questions as $question) {
            $answerCount[$question->getId()] = count($answerRepository->findBy(['question'=>$question->getId()]));
            foreach($question->getSuggestions() as $suggesion) {
                $answerSuggesionCount[$suggesion->getId()] = count($answerRepository->findBy(['answer'=>$suggesion->getId()]));
            }
        }

        return $this->render('questions/index.html.twig', [
            'controller_name' => 'QuestionsController',
            'title' => 'Questionnaires',
            'head' => 'Userarea hallo: '.$this->requestStack->getSession()->get('username'),
            'step' => $step,
            'questions' => $questions,
            'answerCount' => $answerCount,
            'answerSuggesionCount' => $answerSuggesionCount
        ]);
    }

    /**
     * return Steps
     */
    public function getStep($id): Steps
    {
        $repository = $this->doctrine->getRepository(Steps::class);
        $step= $repository->findOneBy(['id'=>$id]);
        return $step;
    }


}
