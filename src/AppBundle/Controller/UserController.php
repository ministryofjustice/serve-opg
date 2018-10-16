<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\PasswordResetForm;
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
     * @Route("/password-reset/request", name="password-reset-request")
     */
    public function passwordResetRequest(Request $request)
    {
        $form = $this->createForm(PasswordResetForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // find user
            // token
            // generate token
            // click and reset


            return $this->redirectToRoute('password-reset-sent');
        }

        return $this->render('AppBundle:User:password-reset-request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/password-reset/sent", name="password-reset-sent")
     */
    public function passwordResetSent(Request $request)
    {
        return $this->render('AppBundle:User:password-reset-sent.html.twig', array(
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
