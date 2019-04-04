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
     * OrdnanceSurveyClient constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {

        $config['apiKey'] = getenv('OS_PLACES_API_KEY');
        
        if (empty($config['apiKey'])) {
            throw new \RuntimeException('OS Places API KEY not found via environment variable');
        }

        parent::__construct($config);
    }
}
