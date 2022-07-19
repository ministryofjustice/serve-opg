<?php

declare(strict_types=1);

namespace App\Controller;

use GuzzleHttp\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    private ClientInterface $httpClient;
    private ParameterStoreService $parameterStoreService;

    public function __construct(ClientInterface $httpClient, ParameterStoreService $parameterStoreService)
    {
        $this->httpClient = $httpClient;
        $this->parameterStoreService = $parameterStoreService;
    }

    public function request(string $type, mixed $data): mixed
    {
        $url = $this->parameterStoreService->getFeatureFlag(ParameterStoreService::FLAG_GO_API_URL);

        $response = $this->httpClient->request($type, $url, [
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
