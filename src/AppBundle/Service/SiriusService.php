<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use Application\Factory\GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use AppBundle\Service\File\Storage\StorageInterface;

class SiriusService
{
    /**
     * @var SiriusClient
     */
    private $httpClient;

    /**
     * @var StorageInterface
     */
    private $localS3Storage;

    /**
     * @var StorageInterface
     */
    private $siriusS3Storage;

    /**
     * SiriusService constructor.
     * @param ClientInterface $httpClient
     * @param StorageInterface $localS3storage
     * @param StorageInterface $siriusS3storage
     */
    public function __construct(
        ClientInterface $httpClient,
        StorageInterface $localS3storage,
        StorageInterface $siriusS3storage
    ) {
        $this->httpClient = $httpClient;
        $this->localS3Storage = $localS3storage;
        $this->siriusS3Storage = $siriusS3storage;
    }

    public function serveOrder(Order $order)
    {
        // copy Documents to Sirius S3 bucket

        // generate JSON payload
        //$payload = $this->generatePayload($order);

        // Make API call
        //$return = $this->login();
        //$return = $this->httpClient->serveOrderToSirius($payload);

    }

    private function login()
    {
        $jar = new \GuzzleHttp\Cookie\CookieJar;

        try {
            $response = $this->httpClient->request(
                'POST',
                'https://localhost:8080/auth/login',
                [
                        'email' => 'manager@opgtest.com',
                        'password' => 'Password1',
//                        'cookies' => $jar
                ]
            );
            echo 'RESPONSE--> ';
            var_dump($response);exit;
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            echo '--------------<br />';
            var_dump($e->getCode());

            var_dump($e->getMessage());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

    }
}
