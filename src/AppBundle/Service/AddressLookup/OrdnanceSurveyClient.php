<?php

namespace AppBundle\Service\AddressLookup;

use GuzzleHttp\Client as GuzzleHttpClient;
use Aws\SecretsManager\SecretsManagerClient;

/**
 * OrdnanceSurveyClient
 */
class OrdnanceSurveyClient extends GuzzleHttpClient
{

    /**
     * @var SecretsManagerClient
     */
    private $secretsManagerClient;

    /**
     * OrdnanceSurveyClient constructor.
     * @param SecretsManagerClient $secretsManagerClient
     * @param array $config
     */
    public function __construct(SecretsManagerClient $secretsManagerClient, array $config)
    {
        $this->secretsManagerClient = $secretsManagerClient;

        $config['apiKey'] = $secretsManagerClient->getSecretValue([
            "SecretId" => 'os_places_api_key'
        ])['SecretString'];
        if (empty($config['apiKey'])) {
            throw new \RuntimeException('OS Places API KEY not found in Secret Manager');
        }

        parent::__construct($config);
    }
}
