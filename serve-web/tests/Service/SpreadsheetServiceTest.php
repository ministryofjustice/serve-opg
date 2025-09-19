<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Service\ClientService;
use App\Service\OrderService;
use App\Service\SpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpreadsheetServiceTest extends KernelTestCase
{
    use ProphecyTrait;
    private string $projectDir;
    private ClientService|ObjectProphecy $clientService;
    private OrderService|ObjectProphecy $orderService;
    private EntityManagerInterface|ObjectProphecy $em;
    private LoggerInterface|ObjectProphecy $logger;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();

        $this->clientService = $this->prophesize(ClientService::class);
        $this->orderService = $this->prophesize(OrderService::class);
        $this->em = $this->prophesize(EntityManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    public function testCsvImportFile()
    {
        $csvFilePath = 'tests/TestData/add_cases.csv';

        $filePath = sprintf('%s/%s', $this->projectDir, $csvFilePath);
        $uploadedFile = new UploadedFile($filePath, 'add_cases.csv', 'text/csv');

        $row1Client = new Client(
            '93559316',
            'Joni Mitchell',
            new \DateTime()
        );

        $row2Client = new Client(
            '93559317',
            'Lorely Rodriguez',
            new \DateTime()
        );

        $this->clientService->upsert('93559316', 'Joni Mitchell')->shouldBeCalled()->willReturn($row1Client);
        $this->clientService->upsert('93559317', 'Lorely Rodriguez')->shouldBeCalled()->willReturn($row2Client);
        $this->clientService->upsert('93559317', 'Lorely Rodriguez')->shouldBeCalled()->willReturn($row2Client);

        $row1Order = new OrderPf(
            $row1Client,
            new \DateTime('1-Aug-2018'),
            new \DateTime('15-Aug-2018'),
            '1'
        );
        $this->orderService->upsert(
            $row1Client,
            OrderPf::class,
            new \DateTime('1-Aug-2018'),
            new \DateTime('15-Aug-2018'),
            '1'
        )->shouldBeCalled()
        ->willReturn($row1Order);

        $row2Order = new OrderHw(
            $row2Client,
            new \DateTime('2-Aug-2018'),
            new \DateTime('17-Aug-2018'),
            '2'
        );
        $this->orderService->upsert(
            $row2Client,
            OrderHw::class,
            new \DateTime('2-Aug-2018'),
            new \DateTime('17-Aug-2018'),
            '2'
        )->shouldBeCalled()
        ->willReturn($row2Order);

        $row3Order = new OrderPf(
            $row2Client,
            new \DateTime('3-Aug-2018'),
            new \DateTime('18-Aug-2018'),
            '3'
        );
        $this->orderService->upsert(
            $row2Client,
            OrderPf::class,
            new \DateTime('3-Aug-2018'),
            new \DateTime('18-Aug-2018'),
            '3'
        )->shouldBeCalled()
            ->willReturn($row3Order);

        $sut = new SpreadsheetService(
            $this->clientService->reveal(),
            $this->orderService->reveal(),
            $this->em->reveal(),
            $this->logger->reveal()
        );

        $sut->importFile($uploadedFile);
    }

    public function testXlsxImportFile()
    {
        $csvFilePath = 'tests/TestData/add_cases.xlsx';

        $filePath = sprintf('%s/%s', $this->projectDir, $csvFilePath);
        $uploadedFile = new UploadedFile(
            $filePath,
            'add_cases.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $row1Client = new Client(
            '93559428',
            'Johnny Depp',
            new \DateTime()
        );

        $row2Client = new Client(
            '93559429',
            'Amber Heard',
            new \DateTime()
        );

        $this->clientService->upsert('93559428', 'Johnny Depp')->shouldBeCalled()->willReturn($row1Client);
        $this->clientService->upsert('93559429', 'Amber Heard')->shouldBeCalled()->willReturn($row2Client);

        $row1Order = new OrderPf(
            $row1Client,
            new \DateTime('31-May-2022'),
            new \DateTime('29-May-2022'),
            '1'
        );
        $this->orderService->upsert(
            $row1Client,
            OrderPf::class,
            new \DateTime('31-May-2022'),
            new \DateTime('29-May-2022'),
            '1'
        )->shouldBeCalled()
        ->willReturn($row1Order);

        $row2Order = new OrderPf(
            $row1Client,
            new \DateTime('31-May-2022'),
            new \DateTime('29-May-2022'),
            '1'
        );
        $this->orderService->upsert(
            $row2Client,
            OrderHw::class,
            new \DateTime('30-May-2022'),
            new \DateTime('28-May-2022'),
            '2'
        )->shouldBeCalled()
        ->willReturn($row2Order);

        $sut = new SpreadsheetService(
            $this->clientService->reveal(),
            $this->orderService->reveal(),
            $this->em->reveal(),
            $this->logger->reveal()
        );

        $sut->importFile($uploadedFile);
    }

    public function testXlsxProcessingDeletionFile()
    {
        $csvFilePath = 'tests/TestData/remove_cases.xlsx';

        $filePath = sprintf('%s/%s', $this->projectDir, $csvFilePath);
        $uploadedFile = new UploadedFile(
            $filePath,
            'remove_cases.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $date = new \DateTime();
        $client1 = new Client('10265617', 'BOBBY FISHER', $date);
        $client1->setId(1);
        $client2 = new Client('10328208', 'MATTHEW CLARKE', $date);
        $client2->setId(2);
        $client3 = new Client('11052088', 'SHERRIE MCARTHUR', $date);
        $client3->setId(3);
        $client4 = new Client('', 'CHARLIE THOMPSON', $date);
        $client4->setId(4);

        $this->clientService->findClientByCaseNumber('10265617')
            ->shouldBeCalled()
            ->willReturn($client1);
        $this->clientService->findClientByCaseNumber('10328208')
            ->shouldBeCalled()
            ->willReturn($client2);
        $this->clientService->findClientByCaseNumber('11052088')
            ->shouldBeCalled()
            ->willReturn($client3);
        $this->clientService->findClientByCaseNumber('11052066')
            ->shouldBeCalled()
            ->willReturn($client4);
        $this->clientService->findClientByCaseNumber('11052077')
            ->shouldBeCalled()
            ->willReturn(null);

        $order1 = new OrderPf($client1, $date, $date, '40002001');
        $order1->setId(1);
        $order2 = new OrderPf($client2, $date, $date, '40002002');
        $order2->setId(2);
        $order3 = new OrderPf($client3, $date, $date, '40002003');
        $order3->setId(3);
        $order4 = new OrderPf($client3, $date, $date, '40002005');
        $order4->setId(4);

        $this->orderService->findPendingOrdersByClient($client1)
            ->shouldBeCalled()
            ->willReturn([$order1]);
        $this->orderService->findPendingOrdersByClient($client2)
            ->shouldBeCalled()
            ->willReturn([$order2]);
        $this->orderService->findPendingOrdersByClient($client3)
            ->shouldBeCalled()
            ->willReturn([$order3, $order4]);
        $this->orderService->findPendingOrdersByClient($client4)
            ->shouldBeCalled()
            ->willReturn([]);

        $sut = new SpreadsheetService(
            $this->clientService->reveal(),
            $this->orderService->reveal(),
            $this->em->reveal(),
            $this->logger->reveal()
        );

        $results = $sut->processDeletionsFile($uploadedFile);
        self::assertCount(3, $results['removeCases']);
        self::assertCount(2, $results['skippedCases']);
    }
}
