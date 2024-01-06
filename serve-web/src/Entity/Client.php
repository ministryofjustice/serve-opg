<?php
namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="client")
 */
class Client
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="case_number", type="string", length=8, unique=true)
     */
    private string $caseNumber;

    /**
     * @ORM\Column(name="client_name", type="string", length=255)
     */
    private string $clientName;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private DateTime $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="client", cascade={"persist"})
     */
    private Collection $orders;

    /**
     * Client constructor.
     * @param string $caseNumber
     * @param string $clientName
     * @param DateTime $createdAt
     */
    public function __construct(string $caseNumber, string $clientName, DateTime $createdAt)
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
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
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
    public function addOrder(Order $order): void
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
        }
    }
}
