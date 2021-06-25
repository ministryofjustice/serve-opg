<?php
namespace App\Entity;

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
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="case_number", type="string", length=8, unique=true)
     */
    private $caseNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="client_name", type="string", length=255)
     */
    private $clientName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var Collection of Order[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="client", cascade={"persist"})
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
