<?php
namespace AppBundle\Entity;

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



}