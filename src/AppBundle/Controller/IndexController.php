<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('case-list');
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
