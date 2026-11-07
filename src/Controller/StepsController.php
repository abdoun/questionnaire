<?php

namespace App\Controller;

use App\Entity\Questionnaires;
use App\Entity\Steps;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route("/steps")]
class StepsController extends AbstractController
{
    public function __construct(private RequestStack $requestStack, private ManagerRegistry $doctrine)
    {
    }

    #[Route("/update", name: "steps_update", methods: ["GET","POST"])]
    public function update(Request $request): Response
    {
        $msg = '';
        $msg_class = 'light';        
        $step = new Steps();
        if($request->get('name')=='') {
            $msg = 'Sorry, you have not filled the required fields!';
            $msg_class = 'danger';
        } else {
            
            $entityManager = $this->doctrine->getManager();
            $msg = 'the step have been just added';

            if($request->get('id')!='') {
                $step = $entityManager->getRepository(Steps::class)->find($request->get('id'));
                $msg = 'the Questionnaire have been just updated';
            }
            $step->setSort($request->get('sort'));
            $step->setName($request->get('name'));
            $step->setActive(($request->get('active') ? true : false));
            $step->setQuestionnaire($this->getQuestionnaire($request->get('questionnaireId')));
            $step->setDescription($request->get('description'));

            $entityManager->persist($step);
            $entityManager->flush();

            
            $msg_class = 'success';
        }
        return $this->render('steps/new.html.twig', [            
            'step'=>$step,
            'msg' => $msg,
            'msg_class' => $msg_class,
            'questionnaireId' => $request->get('questionnaireId')
        ]);
    }

    
    #[Route("/new", name: "steps_new", methods: ["GET"])]
    public function new(Request $request): Response
    {
        $msg = '';
        $msg_class = 'light';
        $step = new Steps();
        if($request->get('id')!='') {
            $repository = $this->doctrine->getRepository(Steps::class);
            $step = $repository->findOneBy(['id'=>$request->get('id')]);
        }

        return $this->render('steps/new.html.twig', [            
            'step'=>$step,
            'msg' => $msg,
            'msg_class' => $msg_class,
            'questionnaireId' => $request->get('questionnaireId')
        ]);
        
    }
    #[Route("/delete/{id}", name: "steps_delete", methods: ["GET"])]
    public function delete($id): Response
    {
        if($id!='') {
            $step = new Steps();
            $entityManager = $this->doctrine->getManager();
            $step = $entityManager->getRepository(Steps::class)->find($id);
            
            $entityManager->remove($step);
            $entityManager->flush();
        }           
        return new Response(null, 204);
    }
    #[Route("/{id}", name: "steps", methods: ["GET"])]
    public function index(Questionnaires $questionnaire)
    {
        if($this->requestStack->getSession()->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }

        $repository = $this->doctrine->getRepository(Steps::class);
        $steps = $repository->findBy(['questionnaire'=>$questionnaire->getId()],array('sort' => 'ASC'));


        return $this->render('steps/index.html.twig', [
            'controller_name' => 'StepsController',
            'title' => 'Steps',
            'head' => 'Userarea hallo: '.$this->requestStack->getSession()->get('username'),
            'steps' => $steps,
            'questionnaire' => $questionnaire
        ]);
    }
    
    /**
     * return obj
     */
    public function getQuestionnaire($id)
    {
        $repository = $this->doctrine->getRepository(Questionnaires::class);
        $questionnaire= $repository->findOneBy(['id'=>$id]);
        return $questionnaire;
    }
}
