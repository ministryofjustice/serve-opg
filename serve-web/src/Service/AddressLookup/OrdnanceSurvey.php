<?php

namespace App\Service\AddressLookup;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\ClientInterface;

class OrdnanceSurvey
{

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var
     */
    private $apiKey;

    /**
     * OrdnanceSurvey constructor.
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient, $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    /**
     * @param $postcode
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function lookupPostcode($postcode)
    {
        $results = $this->getPostcodeData($postcode);
        $addresses = [];
        foreach ($results as $addressData) {
            $address = $this->getAddressLines($addressData['DPA']);
            $address['description'] = $this->getDescription($address);
            $addresses[] = $address;
        }
        return $addresses;

    }

    /**
     * @param $postcode
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getPostcodeData($postcode)
    {
        $url = new Uri($this->httpClient->getConfig('base_uri'));
        $url = URI::withQueryValue($url, 'key', $this->apiKey);
        $url = URI::withQueryValue($url, 'postcode', $postcode);
        $request = new Request('GET', $url);
        $response = $this->httpClient->send($request);

        if ($response->getStatusCode() != 200) {
            throw new \RuntimeException('Error retrieving address details: bad status code');
        }
        $body = json_decode($response->getBody(), true);
        if (isset($body['header']['totalresults']) && $body['header']['totalresults'] === 0) {
            return [];
        }
        if (!isset($body['results']) || !is_array($body['results'])) {
            throw new \RuntimeException('Error retrieving address details: invalid JSON');
        }
        return $body['results'];
    }

    /**
     * Get the address in a standard format of...
     *  [
     *      'addressLine1' => string
     *      'addressLine2' => string
     *      'addressTown' => string
     *      'addressPostcode' => string
     *  ]
     *
     * @param array $address
     * @return array
     */
    private function getAddressLines(array $address)
    {
        $result = [];
        $building = '';

        if(!empty($address['BUILDING_NUMBER'])){
            $building = $address['BUILDING_NUMBER'];
        }
        elseif(!empty($address['BUILDING_NAME'])){
            $building = $address['BUILDING_NAME'];
        }

        $buildingAddress = $building . ' ' . $address['THOROUGHFARE_NAME'];

        $result['addressLine1'] = $buildingAddress;
        $result['addressLine2'] = '';

        if(!empty($address['ORGANISATION_NAME'])){
            $result['addressLine1'] = $address['ORGANISATION_NAME'];
            $result['addressLine2'] = $buildingAddress;
        }

        $result['addressTown'] = $address['POST_TOWN'];
        $result['addressPostcode'] = $address['POSTCODE'];

        return $result;
    }

    /**
     * Get a single line address description (without postcode)
     *
     * @param array $address
     * @return string
     */
    private function getDescription(array $address)
    {
        unset($address['postcode']);
        $address = array_filter($address);
        return trim(implode(', ', $address));
    }

}
