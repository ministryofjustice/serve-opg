<?php

namespace App\Service;

use App\Entity\Deputy;
use App\Entity\Order;
use App\Entity\OrderPf;
use Doctrine\ORM\EntityManager;

class DeputyService
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    // TODO 2024 Remove if not useful
//    /**
//     * Creates and associates a new deputy for a given order type, determined by Deputy.orderType property
//     * that is used to extract the order type from the order
//     *
//     * @param Order $order
//     * @param Deputy $deputy
//     * @throws \Exception
//     */
//    public function createDeputyForOrderType(Order $order, Deputy $deputy)
//    {
//        switch($deputy->getOrderType()) {
//            case Order::TYPE_PA:
//                $orderType = $order->getTypesByOrderType(Order::TYPE_PA);
//                $orderType->addDeputy($deputy);
//
//                $this->em->persist($orderType);
//                break;
//
//            case Order::TYPE_HW:
//                $orderType = $order->getTypesByOrderType(Order::TYPE_HW);
//                $orderType->addDeputy($deputy);
//                $this->em->persist($deputy);
//                break;
//            case Order::TYPE_BOTH:
//                foreach ($order->getTypes() as $orderType) {
//                    $orderType->addDeputy($deputy);
//                    $this->em->persist($deputy);
//                }
//                break;
//            default:
//                throw new \Exception('Unable to create deputy: Order type not known');
//        }
//
//        $this->em->flush();
//
//    }
}
