<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderTypeHw;
use AppBundle\Entity\OrderPa;
use Doctrine\ORM\EntityManager;

class OrderService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * OrderService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }



}