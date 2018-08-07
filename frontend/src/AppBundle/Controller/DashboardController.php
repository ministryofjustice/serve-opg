<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Service\ApiClient\Client;

class DashboardController extends Controller
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        return $this->render('AppBundle:Dashboard:index.html.twig', [
            'user' => $user
        ]);
    }
}