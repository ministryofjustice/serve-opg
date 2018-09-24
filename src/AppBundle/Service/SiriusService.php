<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use Application\Factory\GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class SiriusService
{
    /**
     * @var SiriusClient
     */
    private $httpClient;

    /**
     * SiriusService constructor.
     * @param GuzzleClient $restClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function serveOrder(Order $order)
    {
        $return = $this->login();
        //$payload = $this->generatePayload($order);
        //var_dump($payload);exit;
        //$return = $this->httpClient->serveOrder($order);

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
