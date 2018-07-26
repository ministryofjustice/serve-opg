<?php

namespace AppBundle\Controller;

use AppBundle\Service\ApiClient\Client;

trait ApiTrait
{
    private function apiRequest($method, $uri, array $options = [])
    {
        /* @var $apiClient Client */
        $apiClient = $this->get(Client::class);

        return $apiClient->request($method, $uri, $options);
    }
}

