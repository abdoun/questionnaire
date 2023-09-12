<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;



class Page extends AbstractController 
{
   

    public function __construct()
    {
        
    }
    /**
     * @Route("/", name="homepage")
     */
    public function index() 
    {
        return $this->render('base.html.twig',[
            'title'=>'Heme page'
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about() 
    {
        return $this->render('Page/about.html.twig',[
            'title'=>'About',
            'body'=>'About'
        ]);
    }
    
}