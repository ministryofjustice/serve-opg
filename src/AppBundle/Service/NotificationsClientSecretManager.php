<?php

namespace AppBundle\Service;

/**
 * Alphagov Notifications Client that grabs the apiKey from AWS secret manager
 */
class NotificationsClientSecretManager extends \Alphagov\Notifications\Client
{


    public function __construct(array $config)
    {

        $config['apiKey'] = getenv('NOTIFICATION_API_KEY');

        if (empty($config['apiKey'])) {
            throw new \RuntimeException('Notifications API KEY not passed in via environment variable');
        }

        parent::__construct($config);
    }

}
