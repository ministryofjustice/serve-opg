<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\OrderHw;
use App\exceptions\NoMatchesFoundException;
use App\exceptions\WrongCaseNumberException;
use App\Service\DocumentReaderService;
use App\Service\OrderService;
use App\Service\SiriusService;
use App\Tests\Helpers\FileTestHelper;
use App\Tests\Helpers\OrderTestHelper;
use Doctrine\ORM\EntityManager;
use Exception;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderServiceTest extends WebTestCase
{
    /**
     * @var EntityManager|ObjectProphecy
     */
    private $em;

    /**
     * @var SiriusService|ObjectProphecy
     */
    private $siriusService;

    /**
     * @var DocumentReaderService
     */
    private $documentReader;


    public function setUp(): void
    {
        $this->em = $this->prophesize(EntityManager::class);
        $this->siriusService = $this->prophesize(SiriusService::class);
        $this->documentReader = new DocumentReaderService();
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointAndSeveralNewHw() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointAndSeveralReplacementHw() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING NEW JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('REPLACEMENT_OF_DISCHARGED_DEPUTY', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointNewHw() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING NEW JOINT DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT', $hydratedOrder->getAppointmentType());
        self::assertEquals('REPLACEMENT_OF_DISCHARGED_DEPUTY', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testJointReplacementHw() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING JOINT DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testANewDeputyHw() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING A DEPUTY security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('SOLE', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testAReplacementDeputyHw() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');

        $file = 'No. 93559316 ORDER APPOINTING A NEW DEPUTY security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
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

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
        self::assertEquals('yes', $hydratedOrder->getHasAssetsAboveThreshold());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testBondLevelBelowThreshold() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'PF');

        $file = 'No. 93559316 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £15,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals('no', $hydratedOrder->getHasAssetsAboveThreshold());
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testBondLevelBlank() {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'PF');

        $file = 'No. 93559316 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £ in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
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

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
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

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $sut->answerQuestionsFromText($file, $dehydratedOrder);
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testHydrateOrderFromDocument()
    {
        /** @var EntityManager|ObjectProphecy $em */
        $em = $this->prophesize(EntityManager::class);
        /** @var SiriusService|ObjectProphecy $siriusService */
        $siriusService = $this->prophesize(SiriusService::class);
        $documentReader = new DocumentReaderService();

        $sut = new OrderService($em->reveal(), $siriusService->reveal(), $documentReader);

        $file = FileTestHelper::createUploadedFile('/tests/TestData/validCO - 93559316.docx', 'validCO - 93559316.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '93559316', 'HW');
        $hydratedOrder = $sut->hydrateOrderFromDocument($file, $dehydratedOrder);

        self::assertEquals('JOINT_AND_SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW_APPLICATION', $hydratedOrder->getSubType());
    }
}
