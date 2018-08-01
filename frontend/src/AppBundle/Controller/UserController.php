<?php

namespace AppBundle\Controller;

use AppBundle\Service\ApiClient\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
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
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user

        return $this->render('AppBundle:User:login.html.twig', array(
            'error'         => $error,
        ));
    }

    /**
     * @Route("/logout", name="logout2")
     */
//    public function logoutAction(Request $request)
//    {
//        return new RedirectResponse('/');
//    }
}