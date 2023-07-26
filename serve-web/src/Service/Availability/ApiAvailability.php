<?php

namespace App\Service\Availability;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(private HttpClientInterface $client,)
    {
    }

    public function ping()
    {
        try{
            $response = $this->client->request('GET', 'health-check/service');
            $contentType = $response->getHeaders()['content-type'][0];
            $data = $response->getArrayData();

            if ($contentType != "application/json" || !isset($data['healthy'])) {
                $this->isHealthy = false;
                $this->errors = 'Cannot read API status. '.json_last_error_msg();

                return;
            }

            $this->isHealthy = $data['healthy'];
            $this->errors = $data['errors'];

        } catch (\Throwable $e) {
            $this->isHealthy = false;
            $this->errors = 'Error when connecting to API . '.$e->getMessage();
        }
    }

    public function getName()
    {
        return 'Api';
    }
}
