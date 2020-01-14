<?php

namespace App\Service;

use Alphagov\Notifications\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NotifyClientMock extends Client
{
    public function __construct(array $config)
    {
        return true;
    }

    public function sendEmail($emailAddress, $templateId, array $personalisation = array(), $reference = '', $emailReplyToId = NULL)
    {
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

        return true;
    }

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
