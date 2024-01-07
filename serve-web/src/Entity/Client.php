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

    public function __construct(string $caseNumber, string $clientName, DateTime $createdAt)
    {
        $this->caseNumber = $caseNumber;
        $this->clientName = $clientName;
        $this->createdAt = $createdAt;
        $this->orders = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function hasOrder(string $type): bool
    {
        $orderTypes = [];
        foreach ($this->getOrders() as $order) {
            $orderTypes[] = $order->getType();
        }

        return in_array($type, $orderTypes);
    }

    public function addOrder(Order $order): void
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
        }
    }
}
