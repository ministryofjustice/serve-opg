<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Repository\OrderRepository;
use App\Service\ReportService;
use App\TestHelpers\FileTestHelper;
use App\TestHelpers\OrderTestHelper;
use App\Tests\ApiWebTestCase;
use DateTime;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\PhpUnit\ProphecyTrait;
class ReportServiceTest extends ApiWebTestCase
{
    use ProphecyTrait;

    public function testGenerateCsv()
    {
        $expectedCaseRef = 'COURTREFERENCE1';

        $today = (new DateTime())->format('Y-m-d');
        $minus4Weeks = (new DateTime())->modify('-4 weeks')->format('Y-m-d');

        $filters = [
            'type' => 'served',
            'startDate' => $minus4Weeks,
            'endDate' => $today
        ];

        $client = new Client(
            $expectedCaseRef,
            'Client Name',
            new DateTime()
        );

        $expectedMadeAt = (new DateTime('now'))->format('Y-m-d');
        $expectedIssuedAt = '2019-05-23';
        $expectedServedAt = '2019-05-24';

        $orderPf = new OrderPf(
            $client,
            new DateTime($expectedMadeAt),
            new DateTime($expectedIssuedAt),
            '123'
        );
        $orderPf->setServedAt(new DateTime($expectedServedAt));
        $orderPf->setAppointmentType('JOINT_AND_SEVERAL');

        $orderHw = new OrderHw(
            $client,
            new DateTime($expectedMadeAt),
            new DateTime($expectedIssuedAt),
            '124'
        );
        $orderHw->setServedAt(new DateTime($expectedServedAt));
        $orderHw->setAppointmentType('SOLE');

        $orders = [$orderPf, $orderHw];

        /** @var ObjectProphecy|OrderRepository $orderRepo */
        $orderRepo = $this->prophesize(OrderRepository::class);
        $orderRepo->getOrders($filters, 10000)->shouldBeCalled()->willReturn($orders);

        /** @var ObjectProphecy|EntityManager $em */
        $em = $this->prophesize(EntityManager::class);
        $em->getRepository(Argument::any())->shouldBeCalled()->willReturn($orderRepo->reveal());

        $sut = new ReportService($em->reveal());

        $expectedCsv = <<<CSV
DateIssued,DateMade,DateServed,CaseNumber,AppointmentType,OrderType
$expectedIssuedAt,$expectedMadeAt,$expectedServedAt,$expectedCaseRef,JOINT_AND_SEVERAL,PF
$expectedIssuedAt,$expectedMadeAt,$expectedServedAt,$expectedCaseRef,SOLE,HW

CSV;

        $actualCsv = $sut->generateCsv();
        $actualCsvString = file_get_contents($actualCsv->getRealPath());

        self::assertEquals($expectedCsv, $actualCsvString);
    }

    /**
     * Includes regression test for ensuring 1000 report limit bug is not re-introduced
     */
    public function testCsvLengthEqualsNumberOfCases()
    {
        $em = self::getEntityManager();

        $orders = OrderTestHelper::generateOrders(10000, true);

        $batchSize = 500;

        foreach ($orders as $i => $order) {
            $em->persist($order);

            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear(); // Detaches all objects from Doctrine!
            }
        }

        $em->flush();
        $em->clear();

        $sut = new ReportService($em);
        $csv = $sut->generateCsv();

        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);

        self::assertEquals(10000, $csvRows);
    }

    public function testCsvOnlyReturnsCasesServedWithin4Weeks()
    {
        $em = self::getEntityManager();

        $notServedOrders = OrderTestHelper::generateOrders(10, false);

        $batchSize = 500;

        $notServedOrders[0]->setServedAt((new DateTime())->modify('-2 weeks'));
        $notServedOrders[1]->setServedAt((new DateTime())->modify('-3 weeks'));
        $notServedOrders[2]->setServedAt((new DateTime())->modify('-4 weeks'));

        foreach ($notServedOrders as $i => $order) {
            $em->persist($order);

            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear(); // Detaches all objects from Doctrine!
            }
        }

        $em->flush();
        $em->clear();

        $sut = new ReportService($em);
        $csv = $sut->generateCsv();

        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);

        self::assertEquals(3, $csvRows);
    }

    public function testReportsReturnsAllServedOrders()
    {
        $em = self::getEntityManager();

        $notServedOrders = OrderTestHelper::generateOrders(10, false);

        $batchSize = 500;

        $notServedOrders[0]->setServedAt((new DateTime())->modify('-2 weeks'));
        $notServedOrders[1]->setServedAt((new DateTime())->modify('-3 weeks'));
        $notServedOrders[2]->setServedAt((new DateTime())->modify('-4 weeks'));
        $notServedOrders[3]->setServedAt((new DateTime())->modify('-10 weeks'));
        $notServedOrders[4]->setServedAt((new DateTime())->modify('-1 years'));
        $notServedOrders[5]->setServedAt((new DateTime())->modify('-4 years'));

        foreach ($notServedOrders as $i => $order) {
            $em->persist($order);

            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear(); // Detaches all objects from Doctrine!
            }
        }

        $em->flush();
        $em->clear();

        $sut = new ReportService($em);
        $csv = $sut->generateAllServedOrdersCsv();

        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);

        self::assertEquals(10, $csvRows);
    }

    public function testReportsReturnsAllOrdersNotServed()
    {
        $em = self::getEntityManager();

        $notServedOrders = OrderTestHelper::generateOrders(10, false);

        $batchSize = 500;

        $notServedOrders[0]->setServedAt();
        $notServedOrders[1]->setServedAt();
        $notServedOrders[2]->setServedAt();

        foreach ($notServedOrders as $i => $order) {
            $em->persist($order);

            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear(); // Detaches all objects from Doctrine!
            }
        }

        $em->flush();
        $em->clear();

        $sut = new ReportService($em);
        $csv = $sut->generateOrdersNotServedCsv();

        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);

        self::assertEquals(7, $csvRows);
    }
}
