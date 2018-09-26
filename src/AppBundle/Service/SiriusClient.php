<?php

namespace AppBundle\Service;

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * SiriusClient wrapper
 */
class SiriusClient extends GuzzleHttpClient
{
    /**
     * SiriusClient constructor.
     * @param $args array of arguments
     */
    public function __construct($args)
    {
        parent::__construct($args);
    }
}
