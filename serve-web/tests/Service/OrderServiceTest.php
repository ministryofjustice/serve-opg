<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Order;
use App\exceptions\NoMatchesFoundException;
use App\exceptions\WrongCaseNumberException;
use App\Repository\OrderRepository;
use App\Service\DocumentReaderService;
use App\Service\OrderService;
use App\Service\SiriusService;
use App\TestHelpers\FileTestHelper;
use App\TestHelpers\OrderTestHelper;
use Doctrine\ORM\EntityManager;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderServiceTest extends WebTestCase
{
    use ProphecyTrait;

    private EntityManager|ObjectProphecy $em;
    private SiriusService|ObjectProphecy $siriusService;
    private DocumentReaderService|ObjectProphecy $documentReader;
    private LoggerInterface|ObjectProphecy $logger;

    public function setUp(): void
    {
        $orderRepository = $this->prophesize(OrderRepository::class);

        $this->em = $this->prophesize(EntityManager::class);
        $this->em->getRepository(Order::class)->willReturn($orderRepository->reveal());

        $this->siriusService = $this->prophesize(SiriusService::class);
        $this->documentReader = $this->prophesize(DocumentReaderService::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    public function testAvailabilitySuccess()
    {
        $this->siriusService->ping()->shouldBeCalled()->willReturn(true);
        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        self::assertEquals(true, $sut->isAvailable());
    }

    public function testAvailabilityFailure()
    {
        $this->siriusService->ping()->shouldBeCalled()->willReturn(false);
        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        self::assertEquals(false, $sut->isAvailable());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointAndSeveralNewHw()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointAndSeveralReplacementHw()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING NEW JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('REPLACEMENT_OF_DISCHARGED_DEPUTY', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointNewHw()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING NEW JOINT DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT', $hydratedOrder->getAppointmentType());
        self::assertEquals('REPLACEMENT_OF_DISCHARGED_DEPUTY', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointReplacementHw()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING JOINT DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testANewDeputyHw()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING A DEPUTY security in the sum of £180,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('SOLE', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testAReplacementDeputyHw()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING A NEW DEPUTY security in the sum of £180,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('SOLE', $hydratedOrder->getAppointmentType());
        self::assertEquals('REPLACEMENT_OF_DISCHARGED_DEPUTY', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testAnswerQuestionsFromTextOrderPf()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'PF');

        $file = 'No. 93559316 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
        self::assertEquals('yes', $hydratedOrder->getHasAssetsAboveThreshold());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testBondLevelBelowThreshold()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'PF');

        $file = 'No. 93559316 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £15,000 in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('no', $hydratedOrder->getHasAssetsAboveThreshold());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testBondLevelBlank()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'PF');

        $file = 'No. 93559316 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £ in accordance';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals(null, $hydratedOrder->getHasAssetsAboveThreshold());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testNoMatchesDoesNotUpdateOrder()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'PF');

        $file = 'No. 93559316  RTY AND AFFAIRS';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $sut->answerQuestionsFromText($file, $dehydratedOrder);
        self::assertNull($dehydratedOrder->getSubType());
        self::assertNull($dehydratedOrder->getAppointmentType());
        self::assertNull($dehydratedOrder->getHasAssetsAboveThreshold());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testWrongCaseNumberException()
    {
        self::expectExceptionObject(new WrongCaseNumberException());
        self::expectExceptionMessage('The case number in the document does not match the case number for this order. Please check the file and try again.');

        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', 'WRONGNUMBER', 'PF');

        $file = 'No. 93559316  RTY AND AFFAIRS';

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );
        $sut->answerQuestionsFromText($file, $dehydratedOrder);
    }

    // Removing due to: https://bugs.php.net/bug.php?id=77784
    //    /**
    //     * @throws NoMatchesFoundException
    //     * @throws WrongCaseNumberException
    //     * @throws \Doctrine\ORM\ORMException
    //     * @throws \Doctrine\ORM\OptimisticLockException
    //     */
    //    public function testHydrateOrderFromDocument()
    //    {
    //        /** @var EntityManager|ObjectProphecy $em */
    //        $em = $this->prophesize(EntityManager::class);
    //        /** @var SiriusService|ObjectProphecy $siriusService */
    //        $siriusService = $this->prophesize(SiriusService::class);
    //        $documentReader = new DocumentReaderService();
    //
    //        $sut = new OrderService($em->reveal(), $siriusService->reveal(), $documentReader);
    //
    //        $file = FileTestHelper::createUploadedFile('/tests/TestData/validCO - 93559316.docx', 'validCO - 93559316.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    //
    //        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');
    //        $hydratedOrder = $sut->hydrateOrderFromDocument($file, $dehydratedOrder);
    //
    //        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
    //        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    //    }

    public function testWontServeIfNotReady()
    {
        /** @var Order+ObjectProphecy $order */
        $order = $this->prophesize(Order::class);
        $order->readyToServe()->shouldBeCalled()->willReturn(false);

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Order not ready to be served');

        $sut->serve($order->reveal());
    }

    public function testWontServeIfSiriusUnavailable()
    {
        /** @var Order+ObjectProphecy $order */
        $order = $this->prophesize(Order::class);
        $order->readyToServe()->shouldBeCalled()->willReturn(true);

        $this->siriusService->ping()->shouldBeCalled()->willReturn(false);

        $sut = new OrderService(
            $this->em->reveal(),
            $this->siriusService->reveal(),
            $this->documentReader->reveal(),
            $this->logger->reveal()
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sirius is currently unavailable');

        $sut->serve($order->reveal());
    }
}
