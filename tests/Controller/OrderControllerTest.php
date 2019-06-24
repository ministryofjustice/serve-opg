<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\User;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\OrderTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
{
    /**
     * @var void
     */
    private $testUser;

    /**
     * @var array
     */
    private $basicAuthCreds;

    protected function setUp()
    {
        self::bootKernel();
        $this->purgeDatabase();

        $this->testUser = $this->createTestUser();
        $this->basicAuthCreds = ['PHP_AUTH_USER' => 'test@justice.gov.uk', 'PHP_AUTH_PW'   => 'password123'];
    }

    public function testProcessOrderDocSuccess()
    {
        $this->markTestSkipped(
            'Temporarily skipping to get prototype deployed for UR'
        );

        $order = $this->createOrder(12345, Order::TYPE_HW);

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/validCO - 93559316.docx',
            'validCO - 93559316.docx',
            'application/msword'
        );

        /** @var Client $client */
        $client = $this->getService('test.client');
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file], $this->basicAuthCreds);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertEquals('', $client->getResponse()->getContent());
    }

    public function testProcessOrderDocCaseNumberMismatch()
    {
        $this->markTestSkipped(
            'Temporarily skipping to get prototype deployed for UR'
        );

        $order = $this->createOrder(12345, Order::TYPE_HW);

        $file = FileTestHelper::createUploadedFile(
            '/tests/TestData/validCO - WRONGCASENO.docx',
            'validCO - WRONGCASENO.docx',
            'application/msword'
        );

        /** @var Client $client */
        $client = $this->getService('test.client');
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file], $this->basicAuthCreds);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    /** @dataProvider acceptedDocTypesProvider */
    public function testProcessOrderDocAcceptedFilesNotWord($fileLocation, $originalName, $mimeType)
    {
        $this->markTestSkipped(
            'Temporarily skipping to get prototype deployed for UR'
        );

        $order = $this->createOrder(12345, Order::TYPE_HW);

        $file = FileTestHelper::createUploadedFile($fileLocation, $originalName, $mimeType);

        /** @var Client $client */
        $client = $this->getService('test.client');
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file], $this->basicAuthCreds);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertEquals('Document is not in .doc or .docx format', $client->getResponse()->getContent());
    }

    public function acceptedDocTypesProvider()
    {
        return [
            'jpg' => ['/tests/TestData/test.jpg', 'test.jpg', 'image/jpeg'],
            'jpeg' => ['/tests/TestData/test.jpeg', 'test.jpeg', 'image/jpeg'],
            'pdf' => ['/tests/TestData/test.pdf', 'test.pdf', 'application/pdf'],
            'tiff' => ['/tests/TestData/test.tiff', 'test.tiff', 'image/tiff'],
        ];
    }

    /** @dataProvider partialExtractionProvider
     * @param string $orderType , the type of Order (HW or PF)
     * @param string $fileName , the fixture filename to load
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testProcessOrderDocPartialExtraction(string $orderType, string $fileName)
    {
        $this->markTestSkipped(
            'Temporarily skipping to get prototype deployed for UR'
        );

        $order = $this->createOrder(12345, $orderType);

        $file = FileTestHelper::createUploadedFile(
            "/tests/TestData/${fileName}",
            $fileName,
            'application/msword'
        );

        /** @var Client $client */
        $client = $this->getService('test.client');
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/process-order-doc", [], ['court-order' => $file], $this->basicAuthCreds);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        self::assertEquals('partial data extraction', $client->getResponse()->getContent());
    }

    public function partialExtractionProvider()
    {
        return [
            'Missing SubType' => [
                'HW',
                'Missing sub type - 93559316.docx'
            ],
            'Missing AppointmentType' => [
                'HW',
                'Missing appointment type - 93559316.docx'
            ],
            'Missing HasAssetsAboveThreshold' => [
                'PF',
                'Missing bond amount - 93559316.docx'
            ],
        ];
    }

    /** @dataProvider dataExtractionFormValuesProvider */
    public function testConfirmOrderDetailsDataExtractionResults(
        $subTypeValue,
        $appointmentTypeValue,
        $hasAssetsAboveThresholdValue,
        $missingElementIds,
        $visibleElementId,
        $orderType
    ) {
        $this->markTestSkipped(
            'Temporarily skipping to get prototype deployed for UR'
        );

        $order = $this->createOrder(12345, $orderType);
        $order->setSubType($subTypeValue);
        $order->setAppointmentType($appointmentTypeValue);
        $order->setHasAssetsAboveThreshold($hasAssetsAboveThresholdValue);
        $this->persistEntity($order);

        /** @var Client $client */
        $client = $this->getService('test.client');
        $orderId = $order->getId();
        /** @var Crawler $crawler */
        $crawler = $client->request(Request::METHOD_POST, "/order/${orderId}/confirm-order-details", [], [], $this->basicAuthCreds);

        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        foreach($missingElementIds as $id) {
            self::assertNotContains($id, $client->getResponse()->getContent());
        }

        self::assertContains($visibleElementId, $client->getResponse()->getContent());
    }

    public function dataExtractionFormValuesProvider()
    {
        return [
            'subType not extracted' => [
                null,
                Order::APPOINTMENT_TYPE_JOINT,
                null,
                ['confirm_order_details_form_appointmentType'],
                'confirm_order_details_form_subType',
                'HW'
            ],
            'appointmentType not extracted' => [
                Order::SUBTYPE_NEW,
                null,
                null,
                ['confirm_order_details_form_subType'],
                'confirm_order_details_form_appointmentType',
                'HW'
            ],
            'hasAssetsAboveThreshold not extracted' => [
                Order::SUBTYPE_NEW,
                Order::APPOINTMENT_TYPE_JOINT,
                null,
                ['confirm_order_details_form_appointmentType, confirm_order_details_form_subType'],
                'confirm_order_details_form_hasAssetsAboveThreshold',
                'PF'
            ],
        ];
    }

    protected function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function getService($id)
    {
        return self::$container->get($id);
    }

    /**
     * @param int $id
     * @return OrderHw
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function createOrder(int $id, string $orderType)
    {
        $order = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', $orderType);
        $order->setId($id);
        return $this->persistEntity($order);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }

    protected function persistEntity(object $entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    protected function createTestUser()
    {
        $userModel = new User('test@justice.gov.uk');
        $password = $this->getService('security.user_password_encoder.generic')->encodePassword($userModel, 'password123');
        $userModel->setPassword($password);
        $this->getEntityManager()->persist($userModel);
        $this->getEntityManager()->flush();
    }
}
