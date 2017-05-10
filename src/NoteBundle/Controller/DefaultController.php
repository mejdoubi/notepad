<?php

namespace NoteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function showHomeAction()
    {
        /* ANGULAR */
        return $this->render('NoteBundle:layout_angular.html.twig');
        
        /* SYMFONY
        return $this->redirectToRoute('notes');
        */
    }
}

?>