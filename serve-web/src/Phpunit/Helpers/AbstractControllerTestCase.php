<?php

namespace App\Phpunit\Helpers;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected static $frameworkBundleClient;

    public function setUp(): void
    {
        self::$frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => true]);
    }

     /**
     * @param array $options with keys method, uri, data, mustSucceed, mustFail, assertId
     */
    public function assertJsonRequest($method, $uri, array $options = [], bool $withValidJwt = false): array
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if (isset($options['AuthToken'])) {
            $headers['HTTP_AuthToken'] = $options['AuthToken'];
        }
        if (isset($options['ClientSecret'])) {
            $headers['HTTP_ClientSecret'] = $options['ClientSecret'];
        }

        if ($withValidJwt) {
            $headers['HTTP_JWT'] = $this->jwtService->createNewJWT();
        }

        $rawData = null;
        if (isset($options['data'])) {
            $rawData = json_encode($options['data']);
        } elseif (isset($options['rawData'])) {
            $rawData = $options['rawData'];
        }

        self::$frameworkBundleClient->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            $rawData
        );

        /** @var Response $response */
        $response = self::$frameworkBundleClient->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), 'wrong content type. Headers: '.$headers['CONTENT_TYPE']);

        /** @var string $content */
        $content = $response->getContent();
        $return = json_decode($content, true);
        $this->assertNotEmpty($return, 'Response not json');
        if (!empty($options['mustSucceed'])) {
            $this->assertTrue($return['success'], "Endpoint didn't succeed as expected. Response: ".print_r($return, true));
            if (!empty($options['assertId'])) {
                $this->assertTrue($return['data']['id'] > 0);
            }
        }
        if (!empty($options['mustFail'])) {
            $this->assertFalse($return['success'], "Endpoint didn't fail as expected. Response: ".print_r($return, true));
        }
        if (!empty($options['assertCode'])) {
            $this->assertEquals($options['assertResponseCode'], $return['code'], 'Response: '.print_r($return, true));
        }
        if (!empty($options['assertResponseCode'])) {
            $this->assertEquals($options['assertResponseCode'], $response->getStatusCode(), 'Response: '.$response->getStatusCode().print_r($return, true));
        }

        return $return;
    }
}
