<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\exceptions\NoMatchesFoundException;
use App\exceptions\WrongCaseNumberException;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{


    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testAnswerQuestionsFromTextOrderHw()
    {
        $orderMadeDate = new DateTime('2018-08-01');
        $orderIssuedDate = new DateTime('2018-08-10');
        $client = new Client('1339247T01', 'Bob Bobbins', $orderIssuedDate);
        $order = $this->generateOrderHw($client, $orderMadeDate, $orderIssuedDate);

        $file = 'No. 1339247T01 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $order->answerQuestionsFromText($file);

        self::assertEquals($order->getAppointmentType(), 'JOINT AND SEVERAL');
        self::assertEquals($order->getSubType(), 'NEW ORDER');
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testAnswerQuestionsFromTextOrderPf()
    {
        $orderMadeDate = new DateTime('2018-08-01');
        $orderIssuedDate = new DateTime('2018-08-10');
        $client = new Client('1339247T01', 'Bob Bobbins', $orderIssuedDate);
        $order = $this->generateOrderPf($client, $orderMadeDate, $orderIssuedDate);

        $file = 'No. 1339247T01 ORDER APPOINTING JOINT AND SEVERAL DEPUTIES security in the sum of £180,000 in accordance';

        $order->answerQuestionsFromText($file);

        self::assertEquals($order->getAppointmentType(), 'JOINT AND SEVERAL');
        self::assertEquals($order->getSubType(), 'NEW ORDER');
        self::assertEquals($order->getHasAssetsAboveThreshold(), 'YES');
    }


    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testNoMatchesFoundException()
    {
        self::expectExceptionObject(new NoMatchesFoundException());
        self::expectExceptionMessage('No matches found');

        $orderMadeDate = new DateTime('2018-08-01');
        $orderIssuedDate = new DateTime('2018-08-10');
        $client = new Client('1339247T01', 'Bob Bobbins', $orderIssuedDate);
        $order = $this->generateOrderPf($client, $orderMadeDate, $orderIssuedDate);

        $file = 'No. 1339247T01  RTY AND AFFAIRS';

        $order->answerQuestionsFromText($file);
    }

    /**
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function testWrongCaseNumberException()
    {
        self::expectExceptionObject(new WrongCaseNumberException());
        self::expectExceptionMessage('The order provided does not have the correct case number for 
            this record');

        $orderMadeDate = new DateTime('2018-08-01');
        $orderIssuedDate = new DateTime('2018-08-10');
        $client = new Client('13397T01', 'Bob Bobbins', $orderIssuedDate);
        $order = $this->generateOrderPf($client, $orderMadeDate, $orderIssuedDate);

        $file = 'No. 1339247T01  RTY AND AFFAIRS';

        $order->answerQuestionsFromText($file);
    }

    /**
     * @param $client
     * @param $madeAt
     * @param $issuedAt
     * @return OrderHw
     * @throws Exception
     */
    private function generateOrderHw($client, $madeAt, $issuedAt)
    {
        $order = new OrderHw($client, $madeAt, $issuedAt);
        $order->setSubType('HW');
        return $order;
    }

    /**
     * @param $client
     * @param $madeAt
     * @param $issuedAt
     * @return OrderPf
     * @throws Exception
     */
    private function generateOrderPf($client, $madeAt, $issuedAt)
    {
        $order = new OrderPf($client, $madeAt, $issuedAt);
        $order->setSubType('PF');
        return $order;
    }
}
