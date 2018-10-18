<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Symfony\Component\Routing\RouterInterface;

class MailerService
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $fromEmail;

    /**
     * @var string
     */
    private $fromName;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * MailerService constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param RouterInterface $router
     * @param string $fromEmail
     * @param string $fromName
     */
    public function __construct(\Swift_Mailer $mailer, RouterInterface $router, string $fromEmail, string $fromName)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    /**
     * @param User $user
     *
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    public function sendPasswordResetEmail(User $user)
    {
        /* @var $swiftMessage \Swift_Message */
        $swiftMessage = $this->mailer->createMessage();

        $activationLink = $this->router->generate('password-change', ['token'=>$user->getActivationToken()], RouterInterface::ABSOLUTE_URL);

        $swiftMessage
            ->setTo($user->getEmail(), $user->getUsername())
            ->setFrom($this->fromEmail, $this->fromName)
            ->setSubject('Digicop set user password') // need hybrid title for first activation and normal password reset
            ->setBody('Hi, this is the link to reset your password: ' . $activationLink); // need hybrid body for first activation and normal password reset

        return $this->mailer->send($swiftMessage);
    }


}
