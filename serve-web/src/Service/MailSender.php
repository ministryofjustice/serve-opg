<?php

namespace App\Service;

use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception\NotifyException;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * MailSender with methods to send specific emails using the Gov.UK notify client
 * https://www.notifications.service.gov.uk/
 * https://github.com/alphagov/notifications-php-client.
 *
 * (If a smtp service is needed instead, look at commit ea7dfa2b3122bdae3bc789a9f5f81778645954f3)
 */
class MailSender
{
    public const string FORGOTTEN_PASSWORD_TEMPLATE_ID = 'a7f37a11-d502-4dfa-b7ec-0f0de12e347a';

    public function __construct(
        private readonly Client $notifyClient,
        private readonly RouterInterface $router,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function sendPasswordResetEmail(User $user): void
    {
        if (empty($user->getActivationToken())) {
            throw new \RuntimeException('Cannot send an activation link, the token was not generated');
        }

        $activationLink = $this->router->generate('password-change', ['token' => $user->getActivationToken()], RouterInterface::ABSOLUTE_URL);

        try {
            $this->notifyClient->sendEmail(
                $user->getEmail(),
                self::FORGOTTEN_PASSWORD_TEMPLATE_ID,
                ['activationLink' => $activationLink]
            );
        } catch (NotifyException $e) {
            $this->logger->error('Error while sending email to notify: '.$e->getMessage());
        }
    }
}
