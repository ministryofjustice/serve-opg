<?php declare(strict_types=1);

namespace Tests\phpunit\Entity;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPf;
use DateTime;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{

    /**
     * @dataProvider thresholdProvider
     */
    public function testSetHasAssetsAboveThreshold($formResponse, $expectedValue)
    {
        /** @var Client $client */
        $client = $this->prophesize(Client::class);

        $order = new OrderPf($client->reveal(), new DateTime('now'), new DateTime('now'));
        $order->setHasAssetsAboveThreshold($formResponse);
        self::assertEquals($expectedValue, $order->getHasAssetsAboveThreshold());
    }

    public function thresholdProvider()
    {
        return [
            [Order::HAS_ASSETS_ABOVE_THRESHOLD_YES, Order::HAS_ASSETS_ABOVE_THRESHOLD_YES_SIRIUS],
            [Order::HAS_ASSETS_ABOVE_THRESHOLD_NO, Order::HAS_ASSETS_ABOVE_THRESHOLD_NO_SIRIUS],
            [Order::HAS_ASSETS_ABOVE_THRESHOLD_NA, Order::HAS_ASSETS_ABOVE_THRESHOLD_NA],
            [null, null],
        ];
    }
}
