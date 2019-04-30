<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;

use App\Service\AddressLookup\OrdnanceSurvey;

class OrdnanceSurveyServiceTest extends MockeryTestCase
{

    /**
     * @var MockInterface|Client
     */
    private $httpClient;

    /**
     * @var MockInterface|ResponseInterface
     */
    private $response;

    private $ordnanceSurveyService;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(Client::class);
        $this->httpClient->shouldReceive('getConfig')->with('base_uri');
        $this->httpClient->shouldReceive('getConfig')->with('apiKey');
        $this->httpClient->shouldReceive('getConfig')->with('lr');

        $this->response = Mockery::mock(ResponseInterface::class);

        $this->ordnanceSurveyService = new OrdnanceSurvey($this->httpClient, null);
    }
    //------------------------------------------------------------------------------------

    // Lookup Tests
    public function testHttpLookupUrl()
    {
        $postcode = 'SW1A2AA';

        $this->response->shouldReceive('getStatusCode')->andReturn(200);
        $this->response->shouldReceive('getBody')->andReturn(json_encode([
            'results' => []
        ]));

        $this->httpClient->shouldReceive('send')
            ->withArgs(function ($arg) use ($postcode) {
                // It should be an instance of Request...
                if (!($arg instanceof Request)) {
                    return false;
                }
                // With the postcode in the URL query.
                $query = $arg->getUri()->getQuery();

                if (strpos($query, "postcode={$postcode}") === false) {
                    return false;
                }
                return true;
            })
            ->once()
            ->andReturn($this->response);

        $this->ordnanceSurveyService->lookupPostcode($postcode);
    }
    public function testInvalidHttpLookupResponseCode()
    {
        $postcode = 'SW1A 2AA';
        $this->response->shouldReceive('getStatusCode')->andReturn(500);
        $this->httpClient->shouldReceive('send')
            ->once()
            ->andReturn($this->response);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp( '/bad status code/' );
        $this->ordnanceSurveyService->lookupPostcode($postcode);
    }
    public function testInvalidHttpLookupResponseBody()
    {
        $postcode = 'SW1A 2AA';
        $this->response->shouldReceive('getStatusCode')->andReturn(200);
        $this->response->shouldReceive('getBody')->andReturn('');   // <- Invalid JSON response
        $this->httpClient->shouldReceive('send')
            ->once()
            ->andReturn($this->response);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp( '/invalid JSON/' );
        $this->ordnanceSurveyService->lookupPostcode($postcode);
    }
    public function testValidHttpLookupResponse()
    {
        $postcode = 'SW1A 2AA';
        $this->response->shouldReceive('getStatusCode')->andReturn(200);
        $this->response->shouldReceive('getBody')->andReturn(json_encode([
            'results' => []
        ]));
        $this->httpClient->shouldReceive('send')
            ->once()
            ->andReturn($this->response);
        $result = $this->ordnanceSurveyService->lookupPostcode($postcode);
        // We expect an empty array.
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

}
