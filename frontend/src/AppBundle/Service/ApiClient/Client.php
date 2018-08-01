<?php

namespace AppBundle\Service\ApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class Client
{
    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * ApiClient constructor.
     * @param Client $guzzleClient
     * @param Serializer $serializer
     */
    public function __construct(GuzzleClient $guzzleClient, SerializerInterface $serializer)
    {
        $this->guzzleClient = $guzzleClient;
        $this->serializer = $serializer;
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     *
     * @return stdClass|string
     *
     * @throws ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        try {
            $response = $this->guzzleClient->request($method, $uri, $options); /* @var $response \GuzzleHttp\Psr7\Response */
            $body =$response->getBody();
            if (!empty($options['deserialise_type'])) {

                // avoid double encode/decode if possible
                //http://symfony.com/doc/3.4/components/serializer.html
                $dataJson = json_encode(json_decode($body, true)['data']);
                return $this->serializer->deserialize($dataJson, $options['deserialise_type'], 'json', [
                    'allow_extra_attributes' => true
                ]);
            }

            return (string)$body;

        } catch (ServerException $e) {
            $body = $e->getResponse()->getBody();
            $decodedException = json_decode($body)->exception ?? null;
            throw new ApiException($decodedException->message ?? $body, $decodedException->code ?? 500, $e);
        }
    }


}