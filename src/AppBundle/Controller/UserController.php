<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\PasswordChangeForm;
use AppBundle\Form\PasswordResetForm;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\MailService;
use AppBundle\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @var MailService
     */
    private $mailService;

    /**
     * UserController constructor.
     * @param EntityManager $em
     * @param MailService $mailService
     */
    public function __construct(EntityManager $em, MailService $mailService)
    {
        $this->em = $em;
        $this->mailService = $mailService;
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
        $userRepo = $this->em->getRepository(User::class); /* @var $userRepo UserRepository */

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepo->findOneByEmail($form->getData()['email']);
            if ($user) {
                $userRepo->refreshActivationTokenIfNeeded($user);
                $this->mailService->sendPasswordResetEmail($user);
            }


            return $this->redirectToRoute('password-reset-sent');
        }

        return $this->render('AppBundle:User:password-reset-request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/password-reset/change/{token}", name="password-change")
     */
    public function passwordChange(Request $request, $token)
    {
        $userRepo = $this->em->getRepository(User::class); /* @var $userRepo UserRepository */
        $user = $userRepo->findOneByValidToken($token);
        if (!$user) {
            throw new NotFoundHttpException('Token invalid');
        }

        $form = $this->createForm(PasswordChangeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //TODO change password and redirect to login page with flash message. don't log in, expenisive
        }

        return $this->render('AppBundle:User:password-change.html.twig', [
            'form' => $form->createView(),
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
