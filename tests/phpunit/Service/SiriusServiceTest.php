<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Document;
use AppBundle\Entity\OrderPf;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\SecretsManager\SecretsManagerClient;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

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
//        $this->mockEntityManager = m::mock(EntityManager::class);
        $this->mockEntityManager = $this->prophesize(EntityManager::class);
//        $this->mockHttpClient = $this->generateMockHttpClient();
        $this->mockHttpClient = $this->prophesize(GuzzleClient::class);

//        $this->mockS3Storage = m::mock(S3Storage::class);
        $this->mockS3Storage = $this->prophesize(S3Storage::class);
//        $this->mockLogger = m::mock(LoggerInterface::class);
        $this->mockLogger =  $this->prophesize(LoggerInterface::class);
//        $this->mockSecretsManager = m::mock(SecretsManagerClient::class);
        $this->mockSecretsManager = $this->prophesize(SecretsManagerClient::class);
//        $this->mockSecretsManager->shouldReceive('getSecretValue');
//        $this->mockLogger->shouldReceive('info','debug','error','warning')->zeroOrMoreTimes()->with(m::type('string'))->andReturn('');


//        $this->mockHttpClient->shouldReceive('getConfig')->with('base_uri')->andReturn('FAKE-SIRIUS-URL');
    }

    public function testServeOrderOK()
    {
//        $mockClient = $this->generateMockClient();
        $client = new Client('1234512345', 'AClient Fullname', new DateTime());

        /** @var Order $order */
        $order = $this->generateOrder($client, new DateTime('2018-08-01'), new DateTime('2018-08-10'));
//        $mockOrder->shouldReceive('getClient')->andReturn($mockClient);

        $documents = $order->getDocuments();

//        $this->mockS3Storage->shouldReceive('moveDocuments')
//            ->once()
//            ->with($documents)
//            ->andReturn($documents);
        $this->mockS3Storage->moveDocuments()->shouldBeCalled()->willReturn($documents);
        
//        $this->mockEntityManager->shouldReceive('flush')->once();
        $this->mockEntityManager->flush()->shouldBeCalled();

        $expectedPayload = [
            "courtReference" => Argument::any(),
            "type" => Argument::any(),
            "subType" => Argument::any(),
            "date" => Argument::any(),
            "issueDate" => Argument::any(),
            "appointmentType" => Argument::any(),
            "assetLevel" => 'HIGH',
            'client' => Argument::any(),
            'deputies' => Argument::any(),
            'documents' => Argument::any(),
        ];

        $expectedPost = [
           Argument::any(),
            [
                'json' => $expectedPayload,
                'cookies' => Argument::any()
            ]
        ];

//        $mockHttpClient->shouldReceive('post')->with('auth/login', m::type('array'))->andReturn($mockResponse);
//        $mockHttpClient->shouldReceive('post')->with('auth/logout')->andReturn($mockResponse);
        //        $this->mockHttpClient->shouldReceive('getConfig')->with('base_uri')->andReturn('FAKE-SIRIUS-URL');


        $this->mockHttpClient->post('auth/login')->shouldBeCalled()->willReturn(new Response());
        $this->mockHttpClient->post('auth/logout')->shouldBeCalled()->willReturn(new Response());
        $this->mockHttpClient->getConfig('base_uri')->shouldBeCalled()->willReturn('FAKE-SIRIUS-URL');

        $this->mockHttpClient->post($expectedPost)->shouldBeCalled()->willReturn(new Response());

        $this->sut = new SiriusService(
            $this->mockEntityManager->reveal(),
            $this->mockHttpClient->reveal(),
            $this->mockS3Storage->reveal(),
            $this->mockLogger->reveal(),
            $this->mockSecretsManager->reveal()
        );

        $this->sut->serveOrder($order);

//        $this->mockHttpClient->shouldHaveReceived()
//            ->post()
//            ->withArgs(
//            [
//                Mockery::any(),
//                [
//                    'json' => $expectedPayload,
//                    'cookies' => Mockery::any()
//                ]
//            ]
//        );
    }

    /**
     * Generate mock Order
     *
     * @return m\Mock
     */
    private function generateOrder($client, $madeAt, $issuedAt)
    {
//        $mockOrder = m::mock(OrderPf::class)->makePartial();
        $order = new OrderPf($client, $madeAt, $issuedAt);

//        $mockOrder->shouldReceive('getMadeAt')->andReturn(new \DateTime('2018-08-01'));
//        $mockOrder->shouldReceive('getIssuedAt')->andReturn(new \DateTime('2018-08-10'));
//        $mockOrder->shouldReceive('getHasAssetsAboveThreshold')->andReturn('yes');

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
//        $mockOrder->shouldReceive('getDeputies')->andReturn($mockDeputies);
        $order->setDeputies($mockDeputies);
//        $mockOrder->shouldReceive('getIssuedAt')->andReturn(new \DateTime('2018-08-10'));
//        Already set when instantiating Order
        $order->setDocuments($this->generateMockDocuments(false));

        return $order;
    }

    /**
     * Generates a mock Deputy object
     *
     * @param $options
     * @return ObjectProphecy|Deputy
     */
    private function generateMockDeputy($options)
    {
        /** @var Deputy|ObjectProphecy $mockDeputy */
        $mockDeputy = $this->prophesize(Deputy::class);

        $mockDeputy->getDeputyType()->shouldBeCalled()->willReturn($options['deputyType']);
        $mockDeputy->getForename()->shouldBeCalled()->willReturn('forename' . $options['id']);
        $mockDeputy->getSurname()->shouldBeCalled()->willReturn('surname' . $options['id']);
        $mockDeputy->getDateOfBirth()->shouldBeCalled()->willReturn(new \DateTime('1949-03-19'));
        $mockDeputy->getEmailAddress()->shouldBeCalled()->willReturn('email' . $options['id']);
        $mockDeputy->getDaytimeContactNumber()->shouldBeCalled()->willReturn('DCN' . $options['id']);
        $mockDeputy->getEveningContactNumber()->shouldBeCalled()->willReturn('ECN' . $options['id']);
        $mockDeputy->getMobileContactNumber()->shouldBeCalled()->willReturn('MCN' . $options['id']);
        $mockDeputy->getAddressLine1()->shouldBeCalled()->willReturn('add1-' . $options['id']);
        $mockDeputy->getAddressLine2()->shouldBeCalled()->willReturn('add2-' . $options['id']);
        $mockDeputy->getAddressLine3()->shouldBeCalled()->willReturn('add3-' . $options['id']);
        $mockDeputy->getAddressTown()->shouldBeCalled()->willReturn('town-' . $options['id']);
        $mockDeputy->getAddressCounty()->shouldBeCalled()->willReturn('county-' . $options['id']);
        $mockDeputy->getAddressPostcode()->shouldBeCalled()->willReturn('pc-' . $options['id']);

        return $mockDeputy->reveal();
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
        /** @var Document|ObjectProphecy $mockDoc */
        $mockDoc = $this->prophesize(Document::class);

        if ($options['transferred']) {
//            $mockDoc->shouldReceive('getStorageReference')->andReturn('SIRIUSFILENAME' . $options['id']);
            $mockDoc->getStorageReference()->shouldBeCalled()->willReturn('SIRIUSFILENAME' . $options['id']);
        } else {
//            $mockDoc->shouldReceive('getStorageReference')->andReturn('LOCALFILENAME' . $options['id']);
            $mockDoc->getStorageReference()->shouldBeCalled()->willReturn('LOCALFILENAME' . $options['id']);
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

        return $mockDoc->reveal();
    }

//    private function generateClient()
//    {
//        $client = new Client('1234512345', 'AClient Fullname', new DateTime());
//        $mockClient = m::mock(Client::class);
//
//        $mockClient->shouldReceive('getCaseNumber')->andReturn('1234512345');
//        $mockClient->shouldReceive('getClientName')->andReturn('AClient Fullname');

//        return $client;
//    }

    private function generateMockHttpClient($statusCode = 200)
    {
        $mockResponse = m::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->andReturn($statusCode);
        $mockResponse->shouldReceive('toArray')->andReturn(['statusCode'=> $statusCode]);

        $mockHttpClient = m::spy(ClientInterface::class);

//        $mockHttpClient->shouldReceive('post')
//            ->with(
//                'api/public/v1/orders',
//                m::type('array')
//            )->andReturn($mockResponse);

        $mockHttpClient->shouldReceive('post')->with('auth/login', m::type('array'))->andReturn($mockResponse);
        $mockHttpClient->shouldReceive('post')->with('auth/logout')->andReturn($mockResponse);

        return $mockHttpClient;
    }
}
