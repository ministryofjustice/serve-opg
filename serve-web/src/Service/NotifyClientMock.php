<?php

namespace App\Service;

use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception\ApiException;
use GuzzleHttp\Psr7\Response;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NotifyClientMock extends Client
{
    public static bool $failNext = false;

    /**
     * @throws InvalidArgumentException
     */
    public function sendEmail($emailAddress, $templateId, array $personalisation = [], $reference = '', $emailReplyToId = null, $oneClickUnsubscribeURL = null): array
    {
        if (self::$failNext) {
            self::$failNext = false;
            throw new ApiException('Error sending email', 1, ['errors' => []], new Response());
        }

        $cache = new FilesystemAdapter();
        $emailsItem = $cache->getItem('emails');

        if ($emailsItem->isHit()) {
            $emails = json_decode($emailsItem->get(), true);
        } else {
            $emails = [];
        }

        $emails[] = [
            'to' => $emailAddress,
            'templateId' => $templateId,
            'personalisation' => $personalisation,
        ];

        $emailsItem->set(json_encode($emails));
        $cache->save($emailsItem);

        return $emails;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getLastEmail()
    {
        $cache = new FilesystemAdapter();
        $emailsItem = $cache->getItem('emails');

        if (!$emailsItem->isHit()) {
            return null;
        }

        $emails = json_decode($emailsItem->get(), true);

        return $emails[count($emails) - 1];
    }
}
