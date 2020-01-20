<?php

namespace App\Controller;

use Alphagov\Notifications\Exception\ApiException;
use App\Entity\User;
use App\Form\PasswordChangeForm;
use App\Form\PasswordResetForm;
use App\Form\UserForm;
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
use Throwable;
use Twig\Environment;

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

            $this->addFlash('info', 'Password changed. Please sign in using the new password');

            return $this->redirectToRoute('login');
        }

        return $this->render('User/password-change.html.twig', [
            'isNewUser' => is_null($user->getLastLoginAt()),
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
     * @Route("/users", name="view-users", methods={"GET"})
     */
    public function viewUsers()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->em->getRepository(User::class)->findAll();
        return $this->render('User/view-users.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/users/{id}/view", name="view-user", methods={"GET", "POST"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function viewUser(Request $request, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->em->getRepository(User::class)->find($id);

        if (null === $user) {
            $this->addFlash('error', 'The user does not exist');
            return $this->redirectToRoute('view-users');
        }

        if ($user->getActivationToken() && $user->getLastLoginAt() == null) {
            $activationLink = $this->generateUrl('resend-activation-user', ['id' => $user->getId()]);

            $flashMessage = $this->renderView(
                'FlashMessages\activate-user-reminder.html.twig',
                ['activationLink' => $activationLink]
            );

            $this->addFlash('warn', $flashMessage);
        }
        return $this->render('User/view.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/users/{id}/edit", name="edit-user", methods={"GET", "POST"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function editUser(Request $request, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->em->getRepository(User::class)->find($id);

        if (null === $user) {
            $this->addFlash('error', 'The user does not exist');
            return $this->redirectToRoute('view-users');
        }

        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'User saved');
            return $this->redirectToRoute('view-users');
        }

        return $this->render('User/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/add", name="add-user", methods={"GET", "POST"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function addUser(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User('');

        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword('set-me-up');

            $this->em->persist($user);
            $this->em->flush();

            $userRepo = $this->em->getRepository(User::class); /* @var $userRepo UserRepository */
            $userRepo->refreshActivationToken($user);
            $this->mailerSender->sendPasswordResetEmail($user);

            return $this->redirectToRoute('add-user-confirmation', ['id' => $user->getId()]);
        }

        return $this->render('User/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/{id}/confirmation", name="add-user-confirmation", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function addUserConfirmation(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->em->getRepository(User::class)->find($id);

        if (null === $user) {
            $this->addFlash('error', 'The user does not exist');
            return $this->redirectToRoute('view-users');
        }

        return $this->render('User/add-confirmation.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/users/{id}/resend-activation", name="resend-activation-user", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function resendActivationUser(Request $request, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userRepo = $this->em->getRepository(User::class); /* @var $userRepo UserRepository */
        $user = $userRepo->find($id);

        $userRepo->refreshActivationToken($user);

        try {
            $this->mailerSender->sendPasswordResetEmail($user);
        } catch (ApiException $e) {
            $this->addFlash('error', 'Activation email could not be sent');
            return $this->redirectToRoute('view-user', ['id' => $user->getId()]);
        }

        $this->addFlash('success', 'Activation email resent');
        return $this->redirectToRoute('view-user', ['id' => $user->getId()]);
    }

    /**
     * @Route("/users/{id}/delete", name="delete-user", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function deleteUser(Request $request, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $originUrl = $request->headers->get('referer');

        if ($id === $this->getUser()->getId()) {
            $this->addFlash('error', 'A user cannot delete their own account');
            return $this->redirect($originUrl);
        }

        $user = $this->em->getRepository(User::class)->find($id);

        if (null === $user) {
            $this->addFlash('error', 'The user does not exist');
            return $this->redirect($originUrl);
        }

        try{
            $this->em->getRepository(User::class)->delete($user);
        } catch(Throwable $e) {
            $this->addFlash('error', 'There was an issue deleting the user. Contact the Serve-OPG dev team.');
            return $this->redirect($originUrl);
        }

        $this->addFlash('success', 'User successfully deleted');

        return $this->redirect($originUrl);
    }
}
