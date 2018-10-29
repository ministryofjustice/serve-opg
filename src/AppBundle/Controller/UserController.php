<?php

namespace AppBundle\Controller;

use Alphagov\Notifications\Exception\NotifyException;
use AppBundle\Entity\User;
use AppBundle\Form\PasswordChangeForm;
use AppBundle\Form\PasswordResetForm;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\MailSender;
use AppBundle\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var MailSender
     */
    private $mailerSender;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserController constructor.
     * @param EntityManager $em
     * @param MailSender $mailerSender
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(EntityManager $em, MailSender $mailerSender, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->mailerSender = $mailerSender;
        $this->encoder = $encoder;
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
     * @Route("/user/password-reset/request", name="password-reset-request")
     */
    public function passwordResetRequest(Request $request)
    {
        $form = $this->createForm(PasswordResetForm::class);
        $form->handleRequest($request);
        $userRepo = $this->em->getRepository(User::class); /* @var $userRepo UserRepository */

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];
            $user = $userRepo->findOneByEmail($email); /* @var $user User */
            if ($user) {
                if (empty($user->getActivationToken()) || !$user->isTokenValid()) {
                    $userRepo->refreshActivationToken($user);
                }
                try {
                    $this->mailerSender->sendPasswordResetEmail($user);

                    return $this->redirectToRoute('password-reset-sent', ['email'=>$email]);

                } catch (NotifyException $e){
                    $request->getSession()->getFlashBag()->add('error', 'Sorry, your password could not be reset at the moment.');
                    }
            } else {
                $request->getSession()->getFlashBag()->add('error', 'Sorry, there was a problem with the email address you entered, please try again');
            }

        }

        return $this->render('AppBundle:User:password-reset-request.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/password-reset/change/{token}", name="password-change")
     */
    public function passwordChange(Request $request, $token)
    {
        $userRepo = $this->em->getRepository(User::class); /* @var $userRepo UserRepository */
        $user = $userRepo->findOneBy(['activationToken' => $token]); /* @var $user User */
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        if (!$user->isTokenValid()) {
            throw new NotFoundHttpException('token expired');
        }

        $form = $this->createForm(PasswordChangeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassordEncoded = $this->encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($newPassordEncoded);
            $user->setActivationToken(null);
            $this->em->flush($user);

            $request->getSession()->getFlashBag()->add('info', 'Password changed. Please sign in using the new password');

            return $this->redirectToRoute('login');
        }

        return $this->render('AppBundle:User:password-change.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/user/password-reset/sent", name="password-reset-sent")
     */
    public function passwordResetSent(Request $request)
    {
        return $this->render('AppBundle:User:password-reset-sent.html.twig', [
            'email' => $request->get('email')
        ]);
    }

    /**
     * @Route("/logout", name="logout2")
     */
//    public function logoutAction(Request $request)
//    {
//        return new RedirectResponse('/');
//    }
}
