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
     * @param string $madeAt, in format YYYY-MM-DD
     * @param string $issuedAt, in format YYYY-MM-DD
     * @param string $caseNumber
     * @param string $orderType, HW or PF
     * @return Order
     * @throws Exception
     */
    static public function generateOrder(string $madeAt, string $issuedAt, string $caseNumber, string $orderType)
    {
        $orderMadeDate = new DateTime($madeAt);
        $orderIssuedDate = new DateTime($issuedAt);
        $client = new Client($caseNumber, 'Bob Bobbins', $orderIssuedDate);

        if ($orderType === 'HW') {
            $order = new OrderHw($client, $orderMadeDate, $orderIssuedDate);
        } elseif ($orderType === 'PF') {
            $order = new OrderPf($client, $orderMadeDate, $orderIssuedDate);
        } else {
            throw new Exception('$orderType should be either HW or PF');
        }

        return $order;
    }

    /**
     * @param int $numberOfOrders, amount of orders to generate
     * @param bool $setAsServed, whether to add a servedAt date to orders generated
     * @return Order[]
     * @throws Exception
     */
    static public function generateOrders(int $numberOfOrders, bool $setAsServed)
    {
        $orders = [];
        $lastOrderNumber = 99900000 + $numberOfOrders;
        $issuedAt = new DateTime('2019-01-01');

        for ($i = 99900000; $i < $lastOrderNumber; $i++) {
            $days = $lastOrderNumber - $i;
            $dateString = $issuedAt->add(new DateInterval("P${days}D"))->format('Y-m-d');

            $order = self::generateOrder('2019-01-01', $dateString, (string) $i, 'HW');

            if ($setAsServed) {
                $order->setServedAt(new DateTime($dateString));
            }

            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * @param []Order $orders
     * @return []Order
     */
    protected static function sortOrdersByIssuedAtAscending(array $orders): array
    {
        usort($orders, function($a, $b) {
            strtotime($a->getIssuedAt()->format('Y-m-d')) - strtotime($b->getIssuedAt()->format('Y-m-d'));
        });

        return $orders;
    }

    /**
     * @param array $orders, array or Order objects
     * @param string $datePropertyName , issuedAt|servedAt
     * @return array []Order
     * @throws Exception
     */
    protected static function sortOrdersByDateAscending(array $orders, string $datePropertyName): array
    {
        switch ($datePropertyName){
            case 'issuedAt':
                usort($orders, function($a, $b) {
                    strtotime($a->getIssuedAt()->format('Y-m-d')) - strtotime($b->getIssuedAt()->format('Y-m-d'));
                });
                return $orders;
            case 'servedAt':
                usort($orders, function($a, $b) {
                    strtotime($a->getServedAt()->format('Y-m-d')) - strtotime($b->getServedAt()->format('Y-m-d'));
                });
                return $orders;
            default:
                throw new Exception('$datePropertyName should be either issuedAt or servedAt');
        }
    }

    /**
     * @param []Order $orders
     * @return Order
     */
    public static function getOldestOrderByIssuedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'issuedAt');
        return $sortedOrders[0];
    }

    /**
     * @param []Order $orders
     * @return Order
     */
    public static function getMostRecentOrderByIssuedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'issuedAt');
        return $orders[count($sortedOrders)-1];
    }

    /**
     * @param []Order $orders
     * @return Order
     */
    public static function getOldestOrderByServedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'servedAt');
        return $sortedOrders[0];
    }

    /**
     * @param []Order $orders
     * @return Order
     */
    public static function getMostRecentOrderByServedAt(array $orders): Order
    {
        $sortedOrders = self::sortOrdersByDateAscending($orders, 'servedAt');
        return $orders[count($sortedOrders)-1];
    }
}
