<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Document;
use AppBundle\Entity\OrderPf;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\SecretsManager\SecretsManagerClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class SiriusServiceTest extends MockeryTestCase
{
    /**
     * @var SiriusService
     */
    protected $sut;

    private $mockEntityManager;
    private $mockHttpClient;
    private $mockS3Storage;
    private $mockSecretsManager;

    public function setUp()
    {
        $this->mockEntityManager = m::mock(EntityManager::class);
        $this->mockHttpClient = $this->generateMockHttpClient();
        $this->mockS3Storage = m::mock(S3Storage::class);
        $this->mockLogger = m::mock(LoggerInterface::class);
        $this->mockSecretsManager = m::mock(SecretsManagerClient::class);
        $this->mockSecretsManager->shouldReceive('getSecretValue');
        $this->mockLogger->shouldReceive('info','debug','error','warning')->zeroOrMoreTimes()->with(m::type('string'))->andReturn('');

        $this->mockHttpClient->shouldReceive('getConfig')->with('base_uri')->andReturn('FAKE-SIRIUS-URL');

        $this->sut = new SiriusService(
            $this->mockEntityManager,
            $this->mockHttpClient,
            $this->mockS3Storage,
            $this->mockLogger,
            $this->mockSecretsManager
        );
    }

    public function testServeOrderOK()
    {
        $mockOrder = $this->generateMockOrder();

        $mockClient = $this->generateMockClient('200');

        $mockOrder->shouldReceive('getClient')->andReturn($mockClient);

        $documents = $mockOrder->getDocuments();

        $this->mockS3Storage->shouldReceive('moveDocuments')
            ->once()
            ->with($documents)
            ->andReturn($documents);
        
        $this->mockEntityManager->shouldReceive('flush')->once();

        $this->sut->serveOrder($mockOrder);

    }

    /**
     * Generate mock Order
     *
     * @return m\Mock
     */
    private function generateMockOrder()
    {
        $mockOrder = m::mock(OrderPf::class)->makePartial();

        $mockOrder->shouldReceive('getMadeAt')->andReturn(new \DateTime('2018-08-01'));
        $mockOrder->shouldReceive('getIssuedAt')->andReturn(new \DateTime('2018-08-10'));

        $mockDeputies = new ArrayCollection(
            [
                $this->generateMockDeputy(
                    [
                        'id' => 10,
                        'deputyType' => Deputy::DEPUTY_TYPE_LAY
                    ]
                )
            ]
        );
        $mockOrder->shouldReceive('getDeputies')->andReturn($mockDeputies);
        $mockOrder->shouldReceive('getIssuedAt')->andReturn(new \DateTime('2018-08-10'));
        $mockOrder->setDocuments($this->generateMockDocuments(false));

        return $mockOrder;
    }

    /**
     * Generates a mock Deputy object
     *
     * @param $options
     * @return m\MockInterface
     */
    private function generateMockDeputy($options)
    {
        $mockDeputy = m::mock(Deputy::class);

        $mockDeputy->shouldReceive('getDeputyType')->andReturn($options['deputyType']);
        $mockDeputy->shouldReceive('getForename')->andReturn('forename' . $options['id']);
        $mockDeputy->shouldReceive('getSurname')->andReturn('surname' . $options['id']);
        $mockDeputy->shouldReceive('getDateOfBirth')->andReturn(new \DateTime('1949-03-19'));
        $mockDeputy->shouldReceive('getEmailAddress')->andReturn('email' . $options['id']);
        $mockDeputy->shouldReceive('getDaytimeContactNumber')->andReturn('DCN' . $options['id']);
        $mockDeputy->shouldReceive('getEveningContactNumber')->andReturn('ECN' . $options['id']);
        $mockDeputy->shouldReceive('getMobileContactNumber')->andReturn('MCN' . $options['id']);
        $mockDeputy->shouldReceive('getAddressLine1')->andReturn('add1-' . $options['id']);
        $mockDeputy->shouldReceive('getAddressLine2')->andReturn('add2-' . $options['id']);
        $mockDeputy->shouldReceive('getAddressLine3')->andReturn('add3-' . $options['id']);
        $mockDeputy->shouldReceive('getAddressTown')->andReturn('town-' . $options['id']);
        $mockDeputy->shouldReceive('getAddressCounty')->andReturn('county-' . $options['id']);
        $mockDeputy->shouldReceive('getAddressPostcode')->andReturn('pc-' . $options['id']);

        return $mockDeputy;
    }

    private function generateMockDocuments($transferred = false)
    {
        return new ArrayCollection(
            [
                $this->generateMockDocument(['id' => 20, 'transferred' => $transferred]),
                $this->generateMockDocument(['id' => 21, 'transferred' => $transferred]),
            ]
        );
    }

    /**
     * Generates a mock Document object
     *
     * @param $options
     * @return m\MockInterface
     */
    private function generateMockDocument($options)
    {
        $mockDoc = m::mock(Document::class)->makePartial();

        if ($options['transferred']) {
            $mockDoc->shouldReceive('getStorageReference')->andReturn('SIRIUSFILENAME' . $options['id']);
        } else {
            $mockDoc->shouldReceive('getStorageReference')->andReturn('LOCALFILENAME' . $options['id']);
        }
//        $mockDeputy->shouldReceive('getDeputyType')->andReturn($options['deputyType']);
//        $mockDeputy->shouldReceive('getForename')->andReturn('forename' . $options['id']);
//        $mockDeputy->shouldReceive('getSurname')->andReturn('surname' . $options['id']);
//        $mockDeputy->shouldReceive('getDateOfBirth')->andReturn(new \DateTime('1949-03-19'));
//        $mockDeputy->shouldReceive('getEmailAddress')->andReturn('email' . $options['id']);
//        $mockDeputy->shouldReceive('getDaytimeContactNumber')->andReturn('DCN' . $options['id']);
//        $mockDeputy->shouldReceive('getEveningContactNumber')->andReturn('ECN' . $options['id']);
//        $mockDeputy->shouldReceive('getMobileContactNumber')->andReturn('MCN' . $options['id']);
//        $mockDeputy->shouldReceive('getAddressLine1')->andReturn('add1-' . $options['id']);
//        $mockDeputy->shouldReceive('getAddressLine2')->andReturn('add2-' . $options['id']);
//        $mockDeputy->shouldReceive('getAddressLine3')->andReturn('add3-' . $options['id']);
//        $mockDeputy->shouldReceive('getAddressTown')->andReturn('town-' . $options['id']);
//        $mockDeputy->shouldReceive('getAddressCounty')->andReturn('county-' . $options['id']);
//        $mockDeputy->shouldReceive('getAddressPostcode')->andReturn('pc-' . $options['id']);

        return $mockDoc;
    }

    private function generateMockClient()
    {
        $mockClient = m::mock(Client::class);

        $mockClient->shouldReceive('getCaseNumber')->andReturn('1234512345');
        $mockClient->shouldReceive('getClientName')->andReturn('AClient Fullname');

        return $mockClient;
    }

    private function generateMockHttpClient($statusCode = 200)
    {
        $mockResponse = m::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->andReturn($statusCode);
        $mockResponse->shouldReceive('toArray')->andReturn(['statusCode'=> $statusCode]);

        $mockHttpClient = m::mock(ClientInterface::class);

        $mockHttpClient->shouldReceive('post')
            ->with(
                'api/public/v1/orders',
                m::type('array')
            )->andReturn($mockResponse);

        $mockHttpClient->shouldReceive('post')->with('auth/login', m::type('array'))->andReturn($mockResponse);
        $mockHttpClient->shouldReceive('post')->with('auth/logout')->andReturn($mockResponse);

        return $mockHttpClient;
    }
}
