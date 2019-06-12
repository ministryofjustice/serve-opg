<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use DateTime;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testAnswerQuestionsFromText()
    {
        $orderMadeDate = new DateTime('2018-08-01');
        $orderIssuedDate = new DateTime('2018-08-10');
        $client = new Client('1234567T', 'Bob Bobbins', $orderIssuedDate);
        $order = $this->generateOrder($client, $orderMadeDate, $orderIssuedDate);

        $file = 'No. 1339247T01 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES FOR PROPERTY AND AFFAIRS
                    security in the sum of £180,000 in accordance';

        $order->answerQuestionsFromText($file);

        self::assertEquals($order->getAppointmentType(), 'SOLE');
        self::assertEquals($order->getSubType(), 'NEW ORDER');
    }

    /**
     * @param $client
     * @param $madeAt
     * @param $issuedAt
     * @return OrderHw
     * @throws \Exception
     */
    private function generateOrder($client, $madeAt, $issuedAt)
    {
        $order = new OrderHw($client, $madeAt, $issuedAt);
        $order->setSubType('HW');
        return $order;
    }
}
