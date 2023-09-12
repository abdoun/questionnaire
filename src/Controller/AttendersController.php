<?php

namespace App\Controller;

use App\Entity\Answers;
use App\Entity\Attenders;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Questionnaires;
use App\Entity\Steps;

/**
 * @Route("/attenders")
 */
class AttendersController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    /**
     * @Route("/", name="attenders")
     */
    public function index()
    {
        if($this->session->get('username') === null) {
            return $this->redirectToRoute('users_login');
        }
        return $this->render('attenders/index.html.twig', [
            'controller_name' => 'AttendersController',
        ]);
    }

    /**
     * @Route("/start/{id}", name="attenders_start")
     */
    public function start(Questionnaires $questionnaire, $msg=''): Response
    {
        $steps = $questionnaire->getStep();
        foreach ($steps as $key=>$value) {
            if($value->getActive()){
                $steps[$key]=$value;
            }            
        }
        if($msg !='') {
            return $this->render('attenders/attendForm.html.twig', [
                'title' => 'start participating the questionnaire:'.$questionnaire->getName(),
                'questionnaire'=>$questionnaire,
                'steps' => $steps,
                'msg' => $msg
            ]);
        }
        return $this->render('attenders/start.html.twig', [
            'title' => 'start participating the questionnaire:'.$questionnaire->getName(),
            'questionnaire'=>$questionnaire,
            'steps' => $steps,
            'msg' => $msg
        ]);
    }

    /**
     * @Route("/next/{id}", name="attenders_next")
     */
    public function next(Request $request,Steps $step): Response
    {
        if($request->get('nname') !== null && $request->get('nname') == '') {
            return $this->start($step->getQuestionnaire(), 'Sorry, you have not filled the Name field');
        }
        if($request->get('email') !== null && $request->get('email') == '') {
            return $this->start($step->getQuestionnaire(), 'Sorry, you have not filled the Email field');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $attender = new Attenders();
        //TODO: add to attenders table and get the Id
        if($this->session->get('attender_id') === null) {
            
            
            $attender->setNname($request->get('nname'));
            $attender->setEmail($request->get('email'));
            $attender->setAttendDate( new \DateTime() );
            $entityManager->persist($attender);
            $entityManager->flush();
            $this->session->set('attender_id',$attender->getId());
        } else {
            $attender = $entityManager->getRepository(Attenders::class)->find($this->session->get('attender_id'));
        }
        //var_dump($this->session->get('attender_id'));exit();
        

        $msg = '';
        $msg_class = 'danger';
        $questions = $step->getQuestions();
        foreach ($questions as $key=>$value) {
            if($value->getActive()){
                $questions[$key]=$value;
            }            
        }
        foreach($questions as $question) {
            if($request->get($question->getId()) =='' && $request->get($question->getId()) !== null){
                $msg = 'Sorry, you have not filled all required fields';
                return $this->render('attenders/next.html.twig', [
                    'step' => $step,
                    'msg' => $msg
                ]);
            } 
        }
        $answer = new Answers();
        foreach($questions as $question) {
            if($request->request->get($question->getId()) !=''){
                //TODO: add to the answers table
                $answer->setAnswer($request->request->get($question->getId()));
                $answer->setQuestion($question);
                $answer->setAttender($attender);
                $entityManager->persist($answer);
                $entityManager->flush();
                $msg = 'Next Step';
                $msg_class = 'success';
            }
        }

        if($msg != '') {
            $questionnaire = $step->getQuestionnaire();
            $steps = $questionnaire->getStep();
            foreach ($steps as $key=>$value) {
                if($value->getActive()){
                    $steps[$key]=$value;
                }            
            }
            for($i=0;$i<count($steps);$i++) {
                if($steps[$i]->getId() == $step->getId()) {
                    $step = $steps[$i+1];
                    break;
                }
            }
            
        }
        if($step === null) {
            $this->session->remove('attender_id');
            return new Response('Thanks, End of questionnaire');
        }
        
        return $this->render('attenders/next.html.twig', [
            'step' => $step,
            'msg' => $msg,
            'msg_class' => $msg_class
        ]);
    }
}
