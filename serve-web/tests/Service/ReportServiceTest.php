<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\OrderRepository;
use App\Service\ReportService;
use App\TestHelpers\FileTestHelper;
use App\TestHelpers\OrderTestHelper;
use App\Tests\ApiWebTestCase;
use Doctrine\ORM\EntityManager;

class ReportServiceTest extends ApiWebTestCase
{
    public function testGenerateLast4WeeksCsv()
    {
        $expectedCaseRef = 'COURTREFERENCE1';

        $today = (new \DateTime())->format('Y-m-d');
        $minus4Weeks = (new \DateTime())->modify('-4 weeks')->format('Y-m-d');

        $filters = [
            'type' => 'served',
            'startDate' => $minus4Weeks,
            'endDate' => $today,
        ];

        $expectedMadeAt = (new \DateTime('now'))->format('Y-m-d');
        $expectedIssuedAt = '2019-05-23';
        $expectedServedAt = '2019-05-24';

        $orderPf = [
            'client' => [
                'caseNumber' => $expectedCaseRef,
            ],
            'madeAt' => new \DateTime($expectedMadeAt),
            'issuedAt' => new \DateTime($expectedIssuedAt),
            'servedAt' => new \DateTime($expectedServedAt),
            'type' => 'PF',
            'appointmentType' => 'JOINT_AND_SEVERAL',
        ];

        $orderHw = [
            'client' => [
                'caseNumber' => $expectedCaseRef,
            ],
            'madeAt' => new \DateTime($expectedMadeAt),
            'issuedAt' => new \DateTime($expectedIssuedAt),
            'servedAt' => new \DateTime($expectedServedAt),
            'type' => 'HW',
            'appointmentType' => 'SOLE',
        ];

        $orders = [$orderPf, $orderHw];

        /** @var OrderRepository $orderRepo */
        $orderRepo = $this->createMock(OrderRepository::class);
        $orderRepo->expects($this->once())
            ->method('getOrders')
            ->with($filters)
            ->willReturnCallback(function () use ($orders) {
                foreach ($orders as $order) {
                    yield $order;
                }
            });

        /** @var EntityManager $em */
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($orderRepo);

        $sut = new ReportService($em);

        $expectedCsv = <<<CSV
        DateIssued,DateMade,DateServed,CaseNumber,AppointmentType,OrderType
        $expectedIssuedAt,$expectedMadeAt,$expectedServedAt,$expectedCaseRef,JOINT_AND_SEVERAL,PF
        $expectedIssuedAt,$expectedMadeAt,$expectedServedAt,$expectedCaseRef,SOLE,HW

        CSV;

        $actualCsv = $sut->generateLast4WeeksCsv();
        $actualCsvString = file_get_contents($actualCsv->getRealPath());

        self::assertEquals($expectedCsv, $actualCsvString);
    }

    /**
     * Includes regression test for ensuring 1000 report limit bug is not re-introduced.
     */
    public function testCsvLengthEqualsNumberOfCases()
    {
        $em = self::getEntityManager();

        $orders = OrderTestHelper::generateOrders(2000, true);

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
        $csv = $sut->generateLast4WeeksCsv();

        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);

        self::assertEquals(2000, $csvRows);
    }

    public function testCsvOnlyReturnsCasesServedWithin4Weeks()
    {
        $em = self::getEntityManager();

        $notServedOrders = OrderTestHelper::generateOrders(10, false);

        $batchSize = 500;

        $notServedOrders[0]->setServedAt((new \DateTime())->modify('-2 weeks'));
        $notServedOrders[1]->setServedAt((new \DateTime())->modify('-3 weeks'));
        $notServedOrders[2]->setServedAt((new \DateTime())->modify('-4 weeks'));

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
        $csv = $sut->generateLast4WeeksCsv();

        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);

        self::assertEquals(3, $csvRows);
    }

    protected function numberOfOrdersDataProvider()
    {
        return [
            'tenOrders' => [10],
            'tenThousandOrders' => [10000],
            'thirtyThousandOrders' => [30000],
        ];
    }

    /**
     * @dataProvider numberOfOrdersDataProvider
     */
    public function testReportsReturnsAllServedOrders($numberOfOrders)
    {
        error_log("***** RUNNING TEST CODE *****");
        self::purgeDatabase();
        $em = self::getEntityManager();

        $notServedOrders = OrderTestHelper::generateOrders($numberOfOrders, true);

        $batchSize = 500;

//        $notServedOrders[0]->setServedAt((new \DateTime())->modify('-2 weeks'));
//        $notServedOrders[1]->setServedAt((new \DateTime())->modify('-3 weeks'));
//        $notServedOrders[2]->setServedAt((new \DateTime())->modify('-4 weeks'));
//        $notServedOrders[3]->setServedAt((new \DateTime())->modify('-10 weeks'));
//        $notServedOrders[4]->setServedAt((new \DateTime())->modify('-1 years'));
//        $notServedOrders[5]->setServedAt((new \DateTime())->modify('-4 years'));

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

        self::assertEquals($numberOfOrders, $csvRows);
    }


//    public function testReportsReturnsOnlyUniqueServedOrdersWithSameServedAtDates()
//    {
//        $em = self::getEntityManager();
//
//        $order1 = OrderTestHelper::generateOrder('2023-01-01', '2023-01-01', '88800000', 'PF');
//        $order1->setServedAt((new \DateTime())->modify('-2 weeks'));
//
//        $order2 = OrderTestHelper::generateOrder('2023-01-01', '2023-01-01', '88811111', 'PF');
//        $order2->setClient($order1->getClient());
//        $order2->setOrderNumber($order1->getOrderNumber());
//        $order2->setServedAt($order1->getServedAt());
//
//        $em->persist($order1);
//        $em->persist($order2);
//        $em->flush();
//        $em->clear();
//
//        $sut = new ReportService($em);
//        $csv = $sut->generateAllServedOrdersCsv();
//
//        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);
//
//        self::assertEquals(1, $csvRows);
//    }
//
//    public function testReportsReturnsOnlyUniqueServedOrdersWithDifferentServedAtDates()
//    {
//        $em = self::getEntityManager();
//
//        $order1 = OrderTestHelper::generateOrder('2023-01-01', '2023-01-01', '99900000', 'PF');
//        $order1->setServedAt((new \DateTime())->modify('-2 weeks'));
//
//        $order2 = OrderTestHelper::generateOrder('2023-01-01', '2023-01-01', '99911111', 'PF');
//        $order2->setClient($order1->getClient());
//        $order2->setOrderNumber($order1->getOrderNumber());
//        $order2->setServedAt((new \DateTime())->modify('-1 weeks'));
//
//        $em->persist($order1);
//        $em->persist($order2);
//        $em->flush();
//        $em->clear();
//
//        $sut = new ReportService($em);
//        $csv = $sut->generateAllServedOrdersCsv();
//
//        $csvRows = FileTestHelper::countCsvRows($csv->getRealPath(), true);
//
//        self::assertEquals(2, $csvRows);
//    }
    public function testReportsReturnsAllOrdersNotServed()
    {
        $em = self::getEntityManager();

        $notServedOrders = OrderTestHelper::generateOrders(10, false);

        $batchSize = 500;

        $notServedOrders[0]->setServedAt((new \DateTime())->modify('-2 weeks'));
        $notServedOrders[1]->setServedAt((new \DateTime())->modify('-3 weeks'));
        $notServedOrders[2]->setServedAt((new \DateTime())->modify('-4 weeks'));

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
