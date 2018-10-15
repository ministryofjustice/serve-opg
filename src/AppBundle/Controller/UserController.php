<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils, UserProvider $up)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('AppBundle:User:login.html.twig', array(
            'error' => $error,
            'lockedForSeconds' => $error && ($token = $error->getToken()) && ($username = $token->getUsername())
                ? $up->usernameLockedForSeconds($username)
                : false
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
