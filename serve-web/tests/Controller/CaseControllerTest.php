<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\CaseController;
use App\TestHelpers\OrderTestHelper;
use App\Tests\ApiWebTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;


class CaseControllerTest extends ApiWebTestCase
{
    public function testOrdersToBeServedShowsOldestFiftyOrders()
    {
        $em = $this->getEntityManager();
        $orders = OrderTestHelper::generateOrders(51, false);
        $oldestOrder = OrderTestHelper::getOldestOrderByIssuedAt($orders);
        $mostRecentOrder = OrderTestHelper::getMostRecentOrderByIssuedAt($orders);

        foreach ($orders as $order) {
            $em->persist($order);
        }

        $em->flush();

        /** @var KernelBrowser $client */
        $client = ApiWebTestCase::getService('test.client');
        $crawler = $client->request(Request::METHOD_GET, '/case', [], [], self::BASIC_AUTH_CREDS);

        $tableBody = $crawler->filter('table.govuk-table tbody');
        $rows = $tableBody->filter('tr');

        self::assertEquals(50, $rows->count());
        self::assertStringContainsString($oldestOrder->getIssuedAt()->format('j M Y'), $tableBody->html());
        self::assertStringNotContainsString($mostRecentOrder->getIssuedAt()->format('j M Y') ,$tableBody->html());
    }

    public function testServedOrdersShowsFiftyMostRecentOrders()
    {
        $em = $this->getEntityManager();
        $orders = OrderTestHelper::generateOrders(51, true);
        $oldestOrder = OrderTestHelper::getOldestOrderByServedAt($orders);
        $mostRecentOrder = OrderTestHelper::getMostRecentOrderByServedAt($orders);

        foreach ($orders as $order) {
            $em->persist($order);
        }

        $em->flush();

        /** @var KernelBrowser $client */
        $client = ApiWebTestCase::getService('test.client');
        $crawler = $client->request(Request::METHOD_GET, '/case?type=served', [], [], self::BASIC_AUTH_CREDS);

        $tableBody = $crawler->filter('table.govuk-table tbody');
        $rows = $tableBody->filter('tr');

        self::assertEquals(50, $rows->count());
        self::assertStringContainsString($mostRecentOrder->getIssuedAt()->format('j M Y'), $tableBody->html());
        self::assertStringNotContainsString($oldestOrder->getIssuedAt()->format('j M Y') ,$tableBody->html());
    }
}
