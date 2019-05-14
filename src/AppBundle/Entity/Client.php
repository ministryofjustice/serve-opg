<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class Client
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $caseNumber;

    /**
     * @var string
     */
    private $clientName;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var Collection of Order[]
     */
    private $orders;

    /**
     * Client constructor.
     * @param string $caseNumber
     * @param string $clientName
     * @param \DateTime $createdAt
     */
    public function __construct(string $caseNumber, string $clientName, \DateTime $createdAt)
    {
        $this->caseNumber = $caseNumber;
        $this->clientName = $clientName;
        $this->createdAt = $createdAt;
        $this->orders = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasOrder(string $type)
    {
        $orderTypes = [];
        foreach ($this->getOrders() as $order) {
            $orderTypes[] = $order->getType();
        }

        return in_array($type, $orderTypes);
    }

    /**
     * @param Order $order
     */
    public function addOrder(Order $order)
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
        }
    }
}
