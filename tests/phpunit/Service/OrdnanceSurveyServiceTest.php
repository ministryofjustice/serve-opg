<?php
/**
 * Project: opg-digicop
 * Author: robertford
 * Date: 29/11/2018
 */

namespace AppBundle\Service;

use AppBundle\Service\AddressLookup\OrdnanceSurveyClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;

use AppBundle\Service\AddressLookup\OrdnanceSurvey;

class OrdnanceSurveyServiceTest extends MockeryTestCase
{

    /**
     * @var MockInterface|HttpClientInterface
     */
    private $httpClient;

    /**
     * @var MockInterface|ResponseInterface
     */
    private $response;

    private $ordnanceSurveyService;

    protected function setUp()
    {
        $this->httpClient = Mockery::mock(OrdnanceSurveyClient::class);
        $this->httpClient->shouldReceive('getConfig')->with('base_uri');
        $this->httpClient->shouldReceive('getConfig')->with('key');
        $this->httpClient->shouldReceive('getConfig')->with('lr');

        $this->response = Mockery::mock(ResponseInterface::class);
        $this->ordnanceSurveyService = new OrdnanceSurvey($this->httpClient);
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

    //------------------------------------------------------------------------------------
    // Formatting Tests
    private $testData = [
        [
            'address' => 'GOOD PUB, 10, DRINKING LANE, LONDON', 'postcode' => 'X1 3XX',
            'formatted' => ['GOOD PUB', '10 DRINKING LANE', 'LONDON', 'X1 3XX'],
        ],
        [
            'address' => 'FLAT 1, BOGGLE COURT, 5, TEE PARK, LONDON', 'postcode' => 'X1 3XX',
            'formatted' => ['FLAT 1', 'BOGGLE COURT', '5 TEE PARK, LONDON', 'X1 3XX'],
        ],
        [
            'address' => 'BIG BARN, LONG ROAD, FARMLAND', 'postcode' => 'X1 3XX',
            'formatted' => ['BIG BARN', 'LONG ROAD', 'FARMLAND', 'X1 3XX'],
        ],
        [
            'address' => '4, THE ROAD, LONDON', 'postcode' => 'X1 3XX',
            'formatted' => ['4 THE ROAD', 'LONDON', '', 'X1 3XX'],
        ],
    ];
    private function setupResponse()
    {
        $results = [];
        foreach ($this->testData as $address) {
            $results[] = [
                'DPA' => ['ADDRESS' => "{$address['address']}, {$address['postcode']}", 'POSTCODE'=>$address['postcode']]
            ];
        }
        $this->response->shouldReceive('getStatusCode')->andReturn(200);
        $this->response->shouldReceive('getBody')->andReturn(json_encode([
            'results' => $results
        ]));
    }
    public function testFormatting(){
        $postcode = 'X1 3XX';
        $this->setupResponse();
        $this->httpClient->shouldReceive('send')->once()->andReturn($this->response);
        $results = $this->ordnanceSurveyService->lookupPostcode($postcode);
        $this->assertInternalType('array', $results);
        $this->assertCount(count($this->testData), $results);
        // Loop over each entry in the test data
        foreach ($this->testData as $address) {
            // Get the relating entry from the result
            $result = array_shift($results);
            // For each expected line of formatted address
            foreach($address['formatted'] as $line) {
                // Check it matches the returned formatted line
                $this->assertEquals($line, array_shift($result));
            }
        }
    }

}
