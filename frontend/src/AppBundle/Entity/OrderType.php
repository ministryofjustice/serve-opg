<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var ArrayCollection of Deputy[]
     */
    private $deputys;

    /**
     * OrderType constructor.
     * @param Order $order
     * @param \DateTime $createdAt
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->deputys = new ArrayCollection();
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

    /**
     * @return ArrayCollection
     */
    public function getDeputys()
    {
        return $this->deputys;
    }

    /**
     * @param ArrayCollection $deputys
     */
    public function setDeputys($deputys)
    {
        $this->deputys = $deputys;
    }

    /**
     * @param Deputy $deputy
     */
    public function addDeputy(Deputy $deputy)
    {
        if (!$this->deputys->contains($deputy)) {
            $this->deputys->add($deputy);
        }
    }


}
