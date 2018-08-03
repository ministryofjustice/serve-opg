<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        // deputy homepage with links to register and login
        return $this->render('AppBundle:Index:index.html.twig', [
        ]);
    }

    /**
     * @Route("/design", name="design")
     */
    public function designAction()
    {
        // design stuff
        return $this->render('AppBundle:Index:design.html.twig', [
        ]);
    }

}
