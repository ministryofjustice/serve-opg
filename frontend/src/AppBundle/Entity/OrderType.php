<?php

namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

abstract class OrderType
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Discriminator field
     *
     * @var string
     */
    private $type;

    /**
     * OrderType constructor.
     * @param Order $order
     * @param \DateTime $createdAt
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * @param Order $order
     */
    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }


    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

}