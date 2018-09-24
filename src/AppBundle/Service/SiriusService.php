<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use Application\Factory\GuzzleClient;
use GuzzleHttp\ClientInterface;

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
        return $this->httpClient->serveOrder($order);
    }

}
