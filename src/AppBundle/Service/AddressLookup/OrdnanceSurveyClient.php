<?php

namespace AppBundle\Service\AddressLookup;

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * HttpClient
 */
class OrdnanceSurveyClient extends GuzzleHttpClient
{
    /**
     * HttpClient constructor.
     * @param $args array of arguments
     */
    public function __construct($args)
    {
        parent::__construct($args);
    }
}
