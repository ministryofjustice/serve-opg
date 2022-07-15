<?php

namespace App\Controller;

use App\Service\ParameterStoreService;
use App\Service\Security\LoginAttempts\UserProvider;
use GuzzleHttp\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private UserProvider $userProvider;
    private ClientInterface $httpClient;
    private ParameterStoreService $parameterStoreService;

    public function __construct(UserProvider $userProvider, ClientInterface $httpClient, ParameterStoreService $parameterStoreService)
    {
        $this->userProvider = $userProvider;
        $this->httpClient = $httpClient;
        $this->parameterStoreService = $parameterStoreService;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        $goAuth = $this->parameterStoreService->getFeatureFlag(ParameterStoreService::FLAG_GO_AUTH);

        if ($goAuth) {
            $response = $this->httpClient->request('GET', "http://api:9000/api/auth");
            var_dump($response->getBody()->getContents());
            var_dump($goAuth);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'lockedForSeconds' => $error && ($token = $error->getToken()) && ($lastUsername)
                ? $this->userProvider->usernameLockedForSeconds($lastUsername)
                : false
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
