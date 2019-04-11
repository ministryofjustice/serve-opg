<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Document;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPf;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\SecretsManager\SecretsManagerClient;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
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
        $this->mockEntityManager = $this->prophesize(EntityManager::class);
        $this->mockHttpClient = $this->prophesize(SiriusClient::class);
        $this->mockS3Storage = $this->prophesize(S3Storage::class);
        $this->mockLogger =  $this->prophesize(LoggerInterface::class);
        $this->mockSecretsManager = $this->prophesize(SecretsManagerClient::class);
    }

    public function testServeOrderOK()
    {
        $expectedCourtReference = '1234512345';
        $expectedType = Order::TYPE_PF;
        $expectedOrderStartDate = new DateTime('2018-08-01');
        $expectedOrderIssuedDate = new DateTime('2018-08-10');
        $expectedClientFirstName = 'AClient';
        $expectedClientLastName = 'Fullname';
        $expectedOrderSubType = null;
        $expectedAppointmentType = null;
        $expectedAssetLevel = SiriusService::HAS_ASSETS_ABOVE_THRESHOLD_YES_SIRIUS;
        $expectedClient = ['firstName' => $expectedClientFirstName, 'lastName' => $expectedClientLastName];
        $expectedDeputies = [
            [
                "type" => Deputy::DEPUTY_TYPE_LAY,
                "firstName" => "forename10",
                "lastName" => "surname10",
                "dob" => "1949-03-19",
                "email" => "email10",
                "daytimeNumber" => "DCN10",
                "eveningNumber" => "ECN10",
                "mobileNumber" => "MCN10",
                "addressLine1" => "add1-10",
                "addressLine2" => "add2-10",
                "addressLine3" => "add3-10",
                "town" => "town-10",
                "county" => "county-10",
                "postcode" => "pc-10"
            ]
        ];
        $expectedDocuments = [
            ["type" => "a type", "filename" => "LOCALFILENAME20"],
            ["type" => "a type", "filename" => "LOCALFILENAME21"]
        ];

        $client = new Client(
            $expectedCourtReference,
            sprintf('%s %s', $expectedClientFirstName, $expectedClientLastName),
            new DateTime()
        );

        /** @var OrderPf $order */
        $order = $this->generateOrder($client, $expectedOrderStartDate, $expectedOrderIssuedDate);
        $order->setHasAssetsAboveThreshold('yes');

        $documents = $order->getDocuments();

        $this->mockS3Storage->moveDocuments($documents)->shouldBeCalled()->willReturn($documents);
        
        $this->mockEntityManager->flush()->shouldBeCalled();

        $expectedPayload = [
            "courtReference" => $expectedCourtReference,
            "type" => $expectedType,
            "subType" => $expectedOrderSubType,
            "date" => $expectedOrderStartDate->format('Y-m-d'),
            "issueDate" => $expectedOrderIssuedDate->format('Y-m-d'),
            "appointmentType" => $expectedAppointmentType,
            "assetLevel" => $expectedAssetLevel,
            'client' => $expectedClient,
            'deputies' => $expectedDeputies,
            'documents' => $expectedDocuments,
        ];

        $expectedPost = [
            'json' => $expectedPayload,
            'cookies' => new CookieJar(),
        ];

        $this->mockHttpClient->post('api/public/v1/orders', $expectedPost)->shouldBeCalled()->willReturn(new Response());
        $this->mockHttpClient->post('auth/login', Argument::any())->shouldBeCalled()->willReturn(new Response());
        $this->mockHttpClient->post('auth/logout')->shouldBeCalled()->willReturn(new Response());
        $this->mockHttpClient->getConfig('base_uri')->shouldBeCalled()->willReturn('FAKE-SIRIUS-URL');

        $this->sut = new SiriusService(
            $this->mockEntityManager->reveal(),
            $this->mockHttpClient->reveal(),
            $this->mockS3Storage->reveal(),
            $this->mockLogger->reveal(),
            $this->mockSecretsManager->reveal(),
            null,
            null
        );

        $this->sut->serveOrder($order);
    }

    /**
     * Generate mock Order
     *
     * @return Order
     */
    private function generateOrder($client, $madeAt, $issuedAt)
    {
        $order = new OrderPf($client, $madeAt, $issuedAt);

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
        $order->setDeputies($mockDeputies);

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
     * @return Document|ObjectProphecy
     */
    private function generateMockDocument($options)
    {
        /** @var Document|ObjectProphecy $mockDoc */
        $mockDoc = $this->prophesize(Document::class);
        $mockDoc->getType()->shouldBeCalled()->willReturn('a type');

        if ($options['transferred']) {
            $mockDoc->getStorageReference()->shouldBeCalled()->willReturn('SIRIUSFILENAME' . $options['id']);
        } else {
            $mockDoc->getStorageReference()->shouldBeCalled()->willReturn('LOCALFILENAME' . $options['id']);
        }

        return $mockDoc->reveal();
    }
}
