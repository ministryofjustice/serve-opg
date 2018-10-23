<?php

namespace AppBundle\Service;


use Aws\SecretsManager\SecretsManagerClient;

/**
 * Alphagov Notifications Client that grabs the apiKey from AWS secret manager
 */
class NotificationsClient extends \Alphagov\Notifications\Client
{
    /**
     * @var SecretsManagerClient
     */
    private $secretsManagerClient;

    public function __construct(SecretsManagerClient $secretsManagerClient, array $config)
    {
        $this->secretsManagerClient = $secretsManagerClient;

        $config['apiKey'] = @$secretsManagerClient->getSecretValue([
            "SecretId" => 'notification_api_key'
        ])['SecretString'];
        if (empty($config['apiKey'])) {
            throw new \RuntimeException('Notifications API KEY not found in Secret Manager');
        }

        parent::__construct($config);
    }

}