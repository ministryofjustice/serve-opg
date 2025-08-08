<?php

namespace tests\Service;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Document;
use App\Entity\Order;
use App\Entity\OrderPf;
use App\Service\File\Storage\S3Storage;
use App\Service\SiriusService;
use Aws\CommandInterface;
use Aws\EventBridge\EventBridgeClient;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\SecretsManager\SecretsManagerClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class SiriusServiceTest extends MockeryTestCase
{
    use ProphecyTrait;

    /**
     * @var SiriusService
     */
    protected $sut;

    private $mockEntityManager;
    private $mockHttpClient;
    private $mockS3Storage;
    private $mockSecretsManager;

    public function setUp(): void
    {
        $this->mockEntityManager = $this->prophesize(EntityManager::class);
        $this->mockHttpClient = $this->prophesize(GuzzleHttpClient::class);
        $this->mockS3Storage = $this->prophesize(S3Storage::class);
        $this->mockLogger = $this->prophesize(LoggerInterface::class);
        $this->mockSecretsManager = $this->prophesize(SecretsManagerClient::class);
        $this->mockEventBridge = $this->prophesize(EventBridgeClient::class);
    }

    public function testPingSuccess()
    {
        $this->mockHttpClient->get('health-check/service-status', Argument::cetera())->shouldBeCalled()->willReturn(new Response());

        $this->sut = new SiriusService(
            $this->mockHttpClient->reveal(),
            null,
            null,
            $this->mockEventBridge->reveal(),
            $this->mockLogger->reveal(),
            $this->mockEntityManager->reveal(),
            $this->mockS3Storage->reveal(),
            $this->mockSecretsManager->reveal(),
        );

        $this->assertEquals($this->sut->ping(), true);
    }

    public function testPingFailure()
    {
        $this->mockHttpClient->get('health-check/service-status', Argument::cetera())->shouldBeCalled()->willThrow(new \RuntimeException());

        $this->sut = new SiriusService(
            $this->mockHttpClient->reveal(),
            null,
            null,
            $this->mockEventBridge->reveal(),
            $this->mockLogger->reveal(),
            $this->mockEntityManager->reveal(),
            $this->mockS3Storage->reveal(),
            $this->mockSecretsManager->reveal(),
        );

        $this->assertEquals($this->sut->ping(), false);
    }

    public function testServeOrderOK()
    {
        $expectedCourtReference = '1234512345';
        $expectedType = Order::TYPE_PF;
        $expectedOrderStartDate = new \DateTime('2018-08-01');
        $expectedOrderIssuedDate = new \DateTime('2018-08-10');
        $expectedClientFirstName = 'AClient';
        $expectedClientLastName = 'Fullname';
        $expectedAssetLevel = SiriusService::HAS_ASSETS_ABOVE_THRESHOLD_YES_SIRIUS;
        $expectedClient = ['firstName' => $expectedClientFirstName, 'lastName' => $expectedClientLastName];
        $expectedDeputies = [
            [
                'type' => Deputy::DEPUTY_TYPE_LAY,
                'firstName' => 'forename10',
                'lastName' => 'surname10',
                'dob' => '1949-03-19',
                'email' => 'email10',
                'daytimeNumber' => 'DCN10',
                'eveningNumber' => 'ECN10',
                'mobileNumber' => 'MCN10',
                'addressLine1' => 'add1-10',
                'addressLine2' => 'add2-10',
                'addressLine3' => 'add3-10',
                'town' => 'town-10',
                'county' => 'county-10',
                'postcode' => 'pc-10',
            ],
        ];
        $expectedDocuments = [
            ['type' => 'a type', 'filename' => 'LOCALFILENAME20'],
            ['type' => 'a type', 'filename' => 'LOCALFILENAME21'],
        ];

        $client = new Client(
            $expectedCourtReference,
            sprintf('%s %s', $expectedClientFirstName, $expectedClientLastName),
            new \DateTime()
        );

        /** @var OrderPf $order */
        $order = $this->generateOrder($client, $expectedOrderStartDate, $expectedOrderIssuedDate);
        $order->setHasAssetsAboveThreshold('yes');

        $documents = $order->getDocuments();

        $this->mockS3Storage->moveDocuments($documents)->shouldBeCalled()->willReturn($documents);

        $this->mockEntityManager->flush()->shouldBeCalled();

        $expectedPayload = [
            'courtReference' => $expectedCourtReference,
            'type' => $expectedType,
            'date' => $expectedOrderStartDate->format('Y-m-d'),
            'issueDate' => $expectedOrderIssuedDate->format('Y-m-d'),
            'assetLevel' => $expectedAssetLevel,
            'client' => $expectedClient,
            'deputies' => $expectedDeputies,
            'documents' => $expectedDocuments,
        ];

        $this->mockHttpClient->post('old-login', Argument::any())->shouldBeCalled()->willReturn(
            new Response(200, ['X-XSRF-TOKEN' => 'pKxFAyMS+YXhuDuXB7TlhA=='])
        );

        $expectedPost = [
            'json' => $expectedPayload,
            'cookies' => new CookieJar(),
            'headers' => [
                'X-XSRF-TOKEN' => 'pKxFAyMS+YXhuDuXB7TlhA==',
            ],
        ];

        $this->mockHttpClient->post('api/public/v1/orders', $expectedPost)->shouldBeCalled()->willReturn(new Response());

        $this->mockHttpClient->post('auth/logout')->shouldBeCalled()->willReturn(new Response(401));
        $this->mockHttpClient->getConfig('base_uri')->shouldBeCalled()->willReturn('FAKE-SIRIUS-URL');

        $this->sut = new SiriusService(
            $this->mockHttpClient->reveal(),
            null,
            null,
            $this->mockEventBridge->reveal(),
            $this->mockLogger->reveal(),
            $this->mockEntityManager->reveal(),
            $this->mockS3Storage->reveal(),
            $this->mockSecretsManager->reveal()
        );

        $this->sut->serveOrder($order);
    }

    public function testServeOrderViaEventBusSuccessfully(): void
    {
        $expectedCourtReference = '1234512345';
        $expectedName = 'AClient';
        $expectedOrderStartDate = new \DateTime('2018-08-01');
        $expectedOrderIssuedDate = new \DateTime('2018-08-10');
        $client = $this->prophesize(Client::class);
        $client->getClientName()->willReturn($expectedName);
        $client->getCaseNumber()->willReturn($expectedCourtReference);
        $client->getId()->willReturn(12345);
        $client->addOrder(Argument::type(Order::class))->will(function () {
            // no return needed for void method
        });

        $order = $this->generateOrder($client->reveal(), $expectedOrderStartDate, $expectedOrderIssuedDate);
        $order->setId(12345);
        $order->setHasAssetsAboveThreshold('yes');

        // Mock EventBridge results
        $eventBridgeResult = $this->prophesize(Result::class);
        $eventBridgeResult->toArray()->willReturn(['EventId' => 'abc']);

        $this->mockLogger->info(Argument::containingString('Sending PF Order 12345 via EventBridge'))->shouldBeCalled();
        $this->mockLogger->info(Argument::containingString('Event sent to EventBridge'))->shouldBeCalled();

        $this->mockEntityManager->persist($order)->shouldBeCalled();
        $this->mockEntityManager->flush()->shouldBeCalled();

        $this->mockEventBridge->putEvents(Argument::type('array'))->willReturn($eventBridgeResult->reveal());

        // Act
        $this->sut = new SiriusService(
            $this->mockHttpClient->reveal(),
            null,
            null,
            $this->mockEventBridge->reveal(),
            $this->mockLogger->reveal(),
            $this->mockEntityManager->reveal(),
            $this->mockS3Storage->reveal(),
            $this->mockSecretsManager->reveal()
        );

        $this->sut->serveOrderViaEventBus($order);
    }

    public function testServeOrderViaEventBusThrowsAwsException(): void
    {
        $expectedCourtReference = '1234512345';
        $expectedName = 'AClient';
        $expectedOrderStartDate = new \DateTime('2018-08-01');
        $expectedOrderIssuedDate = new \DateTime('2018-08-10');
        $client = $this->prophesize(Client::class);
        $client->getClientName()->willReturn($expectedName);
        $client->getCaseNumber()->willReturn($expectedCourtReference);
        $client->getId()->willReturn(12345);
        $client->addOrder(Argument::type(Order::class))->will(function () {
            // no return needed for void method
        });

        $order = $this->generateOrder($client->reveal(), $expectedOrderStartDate, $expectedOrderIssuedDate);
        $order->setId(12345);
        $order->setHasAssetsAboveThreshold('yes');

        $command = $this->createMock(CommandInterface::class);
        $request = new Request('POST', 'https://example.com');

        $awsException = new AwsException('AWS error', $command, [
            'request' => $request,
            'response' => null,
            'code' => 500,
            'message' => 'AWS error',
        ]);

        $this->mockEventBridge->putEvents(Argument::type('array'))->willThrow($awsException);

        $this->mockLogger->info(Argument::containingString('Sending PF Order 12345 via EventBridge'))->shouldBeCalled();
        $this->mockLogger->error(Argument::containingString('EventBridge exception: AWS error'))->shouldBeCalled();

        $this->mockEntityManager->persist($order)->shouldBeCalled();
        $this->mockEntityManager->flush()->shouldBeCalled();

        // Act
        $this->sut = new SiriusService(
            $this->mockHttpClient->reveal(),
            null,
            null,
            $this->mockEventBridge->reveal(),
            $this->mockLogger->reveal(),
            $this->mockEntityManager->reveal(),
            $this->mockS3Storage->reveal(),
            $this->mockSecretsManager->reveal()
        );

        $this->expectException(AwsException::class);
        $this->expectExceptionMessage('AWS error');

        $this->sut->serveOrderViaEventBus($order);
    }

    /**
     * Generate mock Order.
     *
     * @return Order
     */
    private function generateOrder($client, $madeAt, $issuedAt)
    {
        $orderId = rand(50000, 100000);
        $orderNumber = strval($orderId);
        $order = new OrderPf($client, $madeAt, $issuedAt, $orderNumber);
        $order->setId($orderId);

        $mockDeputies = new ArrayCollection(
            [
                $this->generateMockDeputy(
                    [
                        'id' => 10,
                        'deputyType' => Deputy::DEPUTY_TYPE_LAY,
                    ]
                ),
            ]
        );
        $order->setDeputies($mockDeputies);

        $order->setDocuments($this->generateMockDocuments(false));

        return $order;
    }

    /**
     * Generates a mock Deputy object.
     *
     * @return ObjectProphecy|Deputy
     */
    private function generateMockDeputy($options)
    {
        /** @var Deputy|ObjectProphecy $mockDeputy */
        $mockDeputy = $this->prophesize(Deputy::class);

        $mockDeputy->getDeputyType()->shouldBeCalled()->willReturn($options['deputyType']);
        $mockDeputy->getForename()->shouldBeCalled()->willReturn('forename'.$options['id']);
        $mockDeputy->getSurname()->shouldBeCalled()->willReturn('surname'.$options['id']);
        $mockDeputy->getDateOfBirth()->shouldBeCalled()->willReturn(new \DateTime('1949-03-19'));
        $mockDeputy->getEmailAddress()->shouldBeCalled()->willReturn('email'.$options['id']);
        $mockDeputy->getDaytimeContactNumber()->shouldBeCalled()->willReturn('DCN'.$options['id']);
        $mockDeputy->getEveningContactNumber()->shouldBeCalled()->willReturn('ECN'.$options['id']);
        $mockDeputy->getMobileContactNumber()->shouldBeCalled()->willReturn('MCN'.$options['id']);
        $mockDeputy->getAddressLine1()->shouldBeCalled()->willReturn('add1-'.$options['id']);
        $mockDeputy->getAddressLine2()->shouldBeCalled()->willReturn('add2-'.$options['id']);
        $mockDeputy->getAddressLine3()->shouldBeCalled()->willReturn('add3-'.$options['id']);
        $mockDeputy->getAddressTown()->shouldBeCalled()->willReturn('town-'.$options['id']);
        $mockDeputy->getAddressCounty()->shouldBeCalled()->willReturn('county-'.$options['id']);
        $mockDeputy->getAddressPostcode()->shouldBeCalled()->willReturn('pc-'.$options['id']);

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
     * Generates a mock Document object.
     *
     * @return Document|ObjectProphecy
     */
    private function generateMockDocument($options)
    {
        /** @var Document|ObjectProphecy $mockDoc */
        $mockDoc = $this->prophesize(Document::class);
        $mockDoc->getType()->shouldBeCalled()->willReturn('a type');

        if ($options['transferred']) {
            $mockDoc->getStorageReference()->shouldBeCalled()->willReturn('SIRIUSFILENAME'.$options['id']);
        } else {
            $mockDoc->getStorageReference()->shouldBeCalled()->willReturn('LOCALFILENAME'.$options['id']);
        }

        return $mockDoc->reveal();
    }
}
