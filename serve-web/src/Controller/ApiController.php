<?php

declare(strict_types=1);

namespace App\Controller;

use GuzzleHttp\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    private ClientInterface $httpClient;

    public function __construct(ClientInterface $httpClient, $apiEndpoint)
    {
        $this->httpClient = $httpClient;
        $this->apiEndpoint = $apiEndpoint;
    }

    public function request(string $type, mixed $data, string $url): mixed
    {
        $url = $this->apiEndpoint .'/'. $url;

        $response = $this->httpClient->request($type, $url, [
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
