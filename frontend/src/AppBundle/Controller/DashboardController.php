<?php

namespace AppBundle\Controller;

use Common\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Service\ApiClient\Client;

class DashboardController extends Controller
{
    /**
     * @var Client
     */
    private $apiClient;

    /**
     * @param Client $apiCllient
     */
    public function __construct(Client $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $this->apiClient->request('GET', '/user/by-id/' . $user->getId(), [
            'deserialise_type' => User::class
        ]);

        return $this->render('AppBundle:Dashboard:index.html.twig', [
            'user' => $user
        ]);
    }
}