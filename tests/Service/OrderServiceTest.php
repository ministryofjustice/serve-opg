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


    public function setUp()
    {
        $this->em = $this->prophesize(EntityManager::class);
        $this->siriusService = $this->prophesize(SiriusService::class);
        $this->documentReader = new DocumentReaderService();
    }

    /**
     * @throws Exception
     */
    public function testAnswerQuestionsFromTextOrderHw()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '1339247T01', 'HW');

        $file = 'No. 1339247T01 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals($hydratedOrder->getAppointmentType(), 'JOINT AND SEVERAL');
        self::assertEquals($hydratedOrder->getSubType(), 'NEW ORDER');
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testAnswerQuestionsFromTextOrderPf()
    {
        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '1339247T01', 'PF');

        $file = 'No. 1339247T01 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $hydratedOrder = $sut->answerQuestionsFromText($file, $dehydratedOrder);

        self::assertEquals($hydratedOrder->getAppointmentType(), 'JOINT AND SEVERAL');
        self::assertEquals($hydratedOrder->getSubType(), 'NEW ORDER');
        self::assertEquals($hydratedOrder->getHasAssetsAboveThreshold(), 'YES');
    }


    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testNoMatchesFoundException()
    {
        self::expectExceptionObject(new NoMatchesFoundException());
        self::expectExceptionMessage('No matches found');

        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '1339247T01', 'PF');

        $file = 'No. 1339247T01  RTY AND AFFAIRS';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $sut->answerQuestionsFromText($file, $dehydratedOrder);
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testWrongCaseNumberException()
    {
        self::expectExceptionObject(new WrongCaseNumberException());
        self::expectExceptionMessage('The order provided does not have the correct case number for this record');

        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', 'WRONGNUMBER', 'PF');

        $file = 'No. 1339247T01  RTY AND AFFAIRS';

        $sut = new OrderService($this->em->reveal(), $this->siriusService->reveal(), $this->documentReader);
        $sut->answerQuestionsFromText($file, $dehydratedOrder);
    }

    public function testHydrateOrderFromDocument()
    {
        /** @var EntityManager|ObjectProphecy $em */
        $em = $this->prophesize(EntityManager::class);
        /** @var SiriusService|ObjectProphecy $siriusService */
        $siriusService = $this->prophesize(SiriusService::class);
        $documentReader = new DocumentReaderService();

        $em->persist(Argument::type(OrderHw::class))->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $sut = new OrderService($em->reveal(), $siriusService->reveal(), $documentReader);

        $file = FileTestHelper::createUploadedFile('/tests/TestData/validCO.docx', 'validCO.docx', 'application/msword');

        $dehydratedOrder = OrderTestHelper::generateOrder('2018-08-01', '2018-08-10', '1339247T01', 'HW');
        $hydratedOrder = $sut->hydrateOrderFromDocument($file, $dehydratedOrder);

        self::assertEquals('JOINT AND SEVERAL', $hydratedOrder->getAppointmentType());
        self::assertEquals('NEW ORDER', $hydratedOrder->getSubType());

    }
}
