<?php

namespace AppBundle\Controller;

use Common\Entity\User;
use AppBundle\Service\ApiClient\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends Controller
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
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        // deputy homepage with links to register and login
        return $this->render('AppBundle:Index:index.html.twig', [
            'debug'=>[]
        ]);
    }

}
