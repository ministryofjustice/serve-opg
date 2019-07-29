<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordChangeForm;
use App\Form\PasswordResetForm;
use App\Repository\UserRepository;
use App\Service\MailSender;
use App\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
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

        return $this->render('User/login.html.twig', array(
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
                $this->mailerSender->sendPasswordResetEmail($user);
            }

            return $this->redirectToRoute('password-reset-sent', ['email'=>$email]);
        }

        return $this->render('User/password-reset-request.html.twig', [
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

        return $this->render('User/password-change.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/user/password-reset/sent", name="password-reset-sent")
     */
    public function passwordResetSent(Request $request)
    {
        return $this->render('User/password-reset-sent.html.twig', [
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

    /**
     * @Route("/users/view", name="view-users", methods={"GET"})
     */
    public function viewUsers()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->em->getRepository(User::class)->findAll();
        return $this->render('User/view-users.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/users/{id}/delete", name="delete-user", methods={"DELETE"})
     * @param int $id
     */
    public function deleteUser(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($id === $this->getUser()->getId()) {
            return new Response(null, Response::HTTP_CONFLICT);
        }

        $user = $this->em->getRepository(User::class)->find($id);

        if (null === $user) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($user);
        $this->em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/users/{id}/delete-refresh", name="delete-user-refresh", methods={"POST"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function deleteUserAndRefresh(Request $request, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $deleteResponse = $this->deleteUser($id);

        $noticeType = 'success';
        $flashMessage = 'User successfully deleted';

        switch ($deleteResponse->getStatusCode()) {
            case Response::HTTP_NO_CONTENT:
                break;
            case Response::HTTP_CONFLICT:
                $noticeType = 'error';
                $flashMessage = 'A user cannot delete their own account';
                break;
            case Response::HTTP_NOT_FOUND:
                $noticeType = 'error';
                $flashMessage = 'The user does not exist';
                break;
            default:
                $noticeType = 'error';
                $flashMessage = 'There was an issue deleting the user. Contact the Serve-OPG dev team.';
                break;
        }

        $this->addFlash($noticeType, $flashMessage);

        $originUrl = $request->headers->get('referer');
        return $this->redirect($originUrl);
    }
}
