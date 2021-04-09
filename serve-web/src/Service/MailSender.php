<?php

namespace App\Service;

use Alphagov\Notifications\Client;
use App\Entity\User;
use Symfony\Component\Routing\RouterInterface;

/**
 * MailSender with methods to send specific emails using the Gov.UK notify client
 * https://www.notifications.service.gov.uk/
 * https://github.com/alphagov/notifications-php-client
 *
 * (If a smtp service is needed instead, look at commit ea7dfa2b3122bdae3bc789a9f5f81778645954f3)
 *
 */
class MailSender
{
    const FORGOTTEN_PASSWORD_TEMPLATE_ID = 'a7f37a11-d502-4dfa-b7ec-0f0de12e347a';

    /**
     * @var null
     */
    private $lastEmailId = null;

    /**
     * @var Client
     */
    private $notifyClient;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * MailSender constructor.
     *
     * @param Client $notifyClient
     * @param RouterInterface $router
     */
    public function __construct(Client $notifyClient, RouterInterface $router)
    {
        $this->notifyClient = $notifyClient;
        $this->router = $router;
    }


    /**
     * @param User $user
     *
     * @return array notify API response
     */
    public function sendPasswordResetEmail(User $user)
    {
        if (empty($user->getActivationToken())) {
            throw new \RuntimeException("Cannot send an activation link, the token was not generated");
        }

        $activationLink = $this->router->generate('password-change', ['token'=>$user->getActivationToken()], RouterInterface::ABSOLUTE_URL);

        $this->lastEmailId = null;

        return $this->getNotifyClient()->sendEmail(
            $user->getEmail(),
            self::FORGOTTEN_PASSWORD_TEMPLATE_ID,
            [
                'activationLink' => $activationLink
            ]
        );
    }

    /**
     * @param string $emailAddress
     *
     * @return Client
     */
    private function getNotifyClient()
    {
        return $this->notifyClient;
    }

    /**
     * @param $notificationId
     *
     * @return null|array [body, email_address, sent_at, status = delivered, subject, template=>[id]]
     */
    public function getLastEmailStatus($notificationId)
    {
        return $this->notifyClient->getNotification($notificationId);
    }

}
