<?php declare(strict_types=1);

namespace App\Tests\Helpers;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use DateTime;
use Exception;

class OrderTestHelper
{
    /**
     * @param string $madeAt
     * @param string $issuedAt
     * @param string $caseNumber
     * @param string $orderType
     * @return OrderHw
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

        $order->setSubType($orderType);
        return $order;
    }
}