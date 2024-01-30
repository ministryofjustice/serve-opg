<?php

namespace tests\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

use App\Service\AddressLookup\OrdnanceSurvey;

class OrdnanceSurveyServiceTest extends MockeryTestCase
{

    /**
     * @var MockInterface|Client
     */
    private $httpClient;

    private $ordnanceSurveyService;

    private string $bodyInvalid;

    private array $bodyValid;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(Client::class);
        $this->httpClient->allows()->getConfig('base_uri');
        $this->httpClient->allows()->getConfig('apiKey');

        $this->ordnanceSurveyService = new OrdnanceSurvey($this->httpClient, 'DUMMY_KEY');

        $this->bodyValid = ['results' => []];
        $this->bodyInvalid = '';
    }
    //------------------------------------------------------------------------------------

    public function testHttpLookupUrl()
    {
        $postcode = 'SW1A2AA';
        $response = new Response(200, [], json_encode($this->bodyValid));

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
            ->andReturn($response);

        $this->ordnanceSurveyService->lookupPostcode($postcode);
    }

    public function testInvalidHttpLookupResponseCode()
    {
        $postcode = 'SW1A 2AA';
        $response = new Response(500);
        $this->httpClient->shouldReceive('send')
            ->once()
            ->andReturn($response);
        $this->expectException(\RuntimeException::class);
        $this->ordnanceSurveyService->lookupPostcode($postcode);
    }

    public function testInvalidHttpLookupResponseBody()
    {
        $postcode = 'SW1A 2AA';
        $response = new Response(200, [], json_encode($this->bodyInvalid));
        $this->httpClient->shouldReceive('send')
            ->once()
            ->andReturn($response);
        $this->expectException(\RuntimeException::class);
        $this->ordnanceSurveyService->lookupPostcode($postcode);
    }

    public function testValidHttpLookupResponse()
    {
        $postcode = 'SW1A 2AA';
        $response = new Response(200, [], json_encode($this->bodyValid));
        $this->httpClient->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $result = $this->ordnanceSurveyService->lookupPostcode($postcode);
        // We expect an empty array.
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

}
