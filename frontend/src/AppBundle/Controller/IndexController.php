<?php

namespace AppBundle\Controller;

use Common\Entity\User;
use AppBundle\Service\ApiClient\Client;
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
            'debug'=>[
                $this->get('em')->getRepository(User::class)->findAll()
            ]
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
