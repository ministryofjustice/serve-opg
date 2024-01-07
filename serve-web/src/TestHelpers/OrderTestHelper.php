<?php declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use DateInterval;
use DateTime;
use Exception;
use http\Exception\InvalidArgumentException;

class OrderTestHelper
{
    /**
     * @param string $madeAt , in format YYYY-MM-DD
     * @param string $issuedAt , in format YYYY-MM-DD
     * @param string $caseNumber
     * @param string $orderType , HW or PF
     * @param string $createdAt
     * @param string|null $orderNumber
     * @throws Exception
     */
    public static function generateOrder(string $madeAt, string $issuedAt, string $caseNumber, string $orderType, string $createdAt = 'now', ?string $orderNumber = null): Order
    {
        $orderMadeDate = new DateTime($madeAt);
        $orderIssuedDate = new DateTime($issuedAt);
        $client = new Client($caseNumber, 'Bob Bobbins', $orderIssuedDate);
        $orderNumber = $orderNumber ?: strval(time() + mt_rand(1,1000000000));

        if ($orderType === 'HW') {
            $order = new OrderHw($client, $orderMadeDate, $orderIssuedDate, $orderNumber, $createdAt);
        } elseif ($orderType === 'PF') {
            $order = new OrderPf($client, $orderMadeDate, $orderIssuedDate, $orderNumber, $createdAt);
        } else {
            throw new Exception('$orderType should be either HW or PF');
        }

        return $order;
    }

    /**
     * @return Order[]
     * @throws Exception
     */
    public static function generateOrders(int $numberOfOrders, bool $setAsServed): array
    {
        $orders = [];
        $lastOrderNumber = 99900000 + $numberOfOrders;
        $issuedAt = new DateTime('2019-01-01');

        for ($i = 99900000; $i < $lastOrderNumber; $i++) {
            $days = $lastOrderNumber - $i;
            $dateString = $issuedAt->add(new DateInterval("P${days}D"))->format('Y-m-d');

            $order = self::generateOrder('2019-01-01', $dateString, (string) $i, 'HW');

            if ($setAsServed) {
                $order->setServedAt((new DateTime())->modify('-1 week')->setTime(0,0,0));
            }

            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * @param array<Order> $orders array or Order objects
     * @return array<Order>
     * @throws Exception
     */
    protected static function sortOrdersByDateAscending(array $orders, string $datePropertyName): array
    {
        switch ($datePropertyName) {
            case 'issuedAt':
                usort($orders, function ($a, $b): int {
                    return strtotime($a->getIssuedAt()->format('Y-m-d')) - strtotime($b->getIssuedAt()->format('Y-m-d'));
                });
                return $orders;
            case 'servedAt':
                usort($orders, function ($a, $b): int {
                    return strtotime($a->getServedAt()->format('Y-m-d')) - strtotime($b->getServedAt()->format('Y-m-d'));
                });
                return $orders;
            default:
                throw new Exception('$datePropertyName should be either issuedAt or servedAt');
        }
    }

    /**
     * @param array<Order> $orders
     * @throws Exception
     */
    public static function getOldestOrderByIssuedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'issuedAt');
        return $sortedOrders[0];
    }

    /**
     * @param array<Order> $orders
     * @throws Exception
     */
    public static function getMostRecentOrderByIssuedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'issuedAt');
        return $orders[count($sortedOrders)-1];
    }

    /**
     * @param array<Order> $orders
     * @throws Exception
     */
    public static function getOldestOrderByServedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'servedAt');
        return $sortedOrders[0];
    }

    /**
     * @param array<Order> $orders
     * @throws Exception
     */
    public static function getMostRecentOrderByServedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'servedAt');
        return $orders[count($sortedOrders)-1];
    }
}
