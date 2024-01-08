<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Table(name: 'deputy')]
#[ORM\Entity]
class Deputy
{
    const DEPUTY_TYPE_LAY = 'LAY';
    const DEPUTY_TYPE_PA = 'PUBLIC_AUTHORITY';
    const DEPUTY_TYPE_PROF = 'PROFESSIONAL';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    private Order $order;

    #[ORM\Column(name: 'deputy_type', type: 'string', length: 255)]
    private ?string $deputyType;

    #[ORM\Column(name: 'forename', type: 'string', length: 255)]
    private string $forename;

    #[ORM\Column(name: 'surname', type: 'string', length: 255)]
    private string $surname;

    #[Assert\NotBlank(message: 'deputy.dateOfBirth.notBlank')]
    #[ORM\Column(name: 'dob', type: 'date', nullable: true)]
    private ?DateTime $dateOfBirth;

    #[Assert\NotBlank(message: 'deputy.emailAddress.notBlank')]
    #[ORM\Column(name: 'email_address', type: 'string', length: 255, nullable: true)]
    private ?string $emailAddress;

    #[ORM\Column(name: 'daytime_contact_number', type: 'string', length: 255, nullable: true)]
    private ?string $daytimeContactNumber;

    #[ORM\Column(name: 'evening_contact_number', type: 'string', length: 255, nullable: true)]
    private ?string $eveningContactNumber;

    #[ORM\Column(name: 'mobile_contact_number', type: 'string', length: 255, nullable: true)]
    private ?string $mobileContactNumber;

    #[Assert\NotBlank(message: 'deputy.address_line_1.notBlank')]
    #[ORM\Column(name: 'address_line_1', type: 'string', length: 255, nullable: true)]
    private ?string $addressLine1;

    #[ORM\Column(name: 'address_line_2', type: 'string', length: 255, nullable: true)]
    private ?string $addressLine2;

    #[ORM\Column(name: 'address_line_3', type: 'string', length: 255, nullable: true)]
    private ?string $addressLine3;

    #[Assert\NotBlank(message: 'deputy.address_town.notBlank')]
    #[ORM\Column(name: 'address_town', type: 'string', length: 255, nullable: true)]
    private ?string $addressTown;

    #[ORM\Column(name: 'address_county', type: 'string', length: 255, nullable: true)]
    private ?string $addressCounty;

    #[Assert\NotBlank(message: 'deputy.address_postcode.notBlank')]
    #[ORM\Column(name: 'address_postcode', type: 'string', length: 255, nullable: true)]
    private ?string $addressPostcode;

    private ?string $addressCountry;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function getDeputyType(): ?string
    {
        return $this->deputyType;
    }

    public function setDeputyType(?string $deputyType): static
    {
        $this->deputyType = $deputyType;
        return $this;
    }

    public function getForename(): string
    {
        return $this->forename;
    }

    public function setForename(string $forename): static
    {
        $this->forename = $forename;
        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;
        return $this;
    }

    public function getFullname(): string
    {
        return $this->forename . ' ' . $this->surname;
    }

    public function getDateOfBirth(): ?DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?DateTime $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function getDaytimeContactNumber(): ?string
    {
        return $this->daytimeContactNumber;
    }

    public function setDaytimeContactNumber(?string $daytimeContactNumber): static
    {
        $this->daytimeContactNumber = $daytimeContactNumber;
        return $this;
    }

    public function getEveningContactNumber(): ?string
    {
        return $this->eveningContactNumber;
    }

    public function setEveningContactNumber(?string $eveningContactNumber): static
    {
        $this->eveningContactNumber = $eveningContactNumber;
        return $this;
    }

    public function getMobileContactNumber(): ?string
    {
        return $this->mobileContactNumber;
    }

    public function setMobileContactNumber(?string $mobileContactNumber): static
    {
        $this->mobileContactNumber = $mobileContactNumber;
        return $this;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): static
    {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    public function getAddressLine3(): ?string
    {
        return $this->addressLine3;
    }

    public function setAddressLine3(?string $addressLine3): static
    {
        $this->addressLine3 = $addressLine3;
        return $this;
    }

    public function getAddressTown(): ?string
    {
        return $this->addressTown;
    }

    public function setAddressTown(?string $addressTown): static
    {
        $this->addressTown = $addressTown;
        return $this;
    }

    public function getAddressCounty(): ?string
    {
        return $this->addressCounty;
    }

    public function setAddressCounty(?string $addressCounty): static
    {
        $this->addressCounty = $addressCounty;
        return $this;
    }

    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    public function setAddressPostcode(?string $addressPostcode): static
    {
        $this->addressPostcode = $addressPostcode;
        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): static
    {
        $this->addressCountry = $addressCountry;
        return $this;
    }

    public function getAddressFormatted(): string
    {
        return implode(', ', array_filter([
            $this->getAddressLine1(),
            $this->getAddressLine2(),
            $this->getAddressLine3(),
            $this->getAddressTown(),
            $this->getAddressCounty(),
            $this->getAddressPostcode(),
            $this->getAddressCountry()
        ]));
    }
}
