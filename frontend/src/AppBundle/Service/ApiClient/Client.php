<?php

namespace AppBundle\Service\ApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ServerException;

class Client
{
    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * ApiClient constructor.
     * @param Client $guzzleClient
     */
    public function __construct(GuzzleClient $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @throws ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        try {
            $this->guzzleClient->request($method, $uri, $options);
        } catch (ServerException $e) {
            $body = $e->getResponse()->getBody();
            $decodedException = json_decode($body)->exception ?? null;
            throw new ApiException($decodedException->message ?? $body, $decodedException->code ?? 500, $e);
        }
    }


}