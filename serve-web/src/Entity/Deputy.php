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


/**
 * @ORM\Entity
 * @ORM\Table(name="deputy")
 */
class Deputy
{
    const DEPUTY_TYPE_LAY = 'LAY';
    const DEPUTY_TYPE_PA = 'PUBLIC_AUTHORITY';
    const DEPUTY_TYPE_PROF = 'PROFESSIONAL';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    private Order $order;

    /**
     * @ORM\Column(name="deputy_type", type="string", length=255)
     */
    private ?string $deputyType;

    /**
     * @ORM\Column(name="forename", type="string", length=255)
     */
    private string $forename;

    /**
     * @ORM\Column(name="surname", type="string", length=255)
     */
    private string $surname;

    /**
     * @ORM\Column(name="dob", type="date", nullable=true)
     * @Assert\NotBlank(message="deputy.dateOfBirth.notBlank")
     */
    private ?DateTime $dateOfBirth;

    /**
     * @ORM\Column(name="email_address", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.emailAddress.notBlank")
     */
    private ?string $emailAddress;

    /**
     * @ORM\Column(name="daytime_contact_number", type="string", length=255, nullable=true)
     */
    private ?string $daytimeContactNumber;

    /**
     * @ORM\Column(name="evening_contact_number", type="string", length=255, nullable=true)
     */
    private ?string $eveningContactNumber;

    /**
     * @ORM\Column(name="mobile_contact_number", type="string", length=255, nullable=true)
     */
    private ?string $mobileContactNumber;

    /**
     * @ORM\Column(name="address_line_1", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.address_line_1.notBlank")
     */
    private ?string $addressLine1;

    /**
     * @ORM\Column(name="address_line_2", type="string", length=255, nullable=true)
     */
    private ?string $addressLine2;

    /**
     * @ORM\Column(name="address_line_3", type="string", length=255, nullable=true)
     */
    private ?string $addressLine3;

    /**
     * @ORM\Column(name="address_town", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.address_town.notBlank")
     */
    private ?string $addressTown;

    /**
     * @ORM\Column(name="address_county", type="string", length=255, nullable=true)
     */
    private ?string $addressCounty;

    /**
     * @ORM\Column(name="address_postcode", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.address_postcode.notBlank")
     */
    private ?string $addressPostcode;

    private ?string $addressCountry;

    /**
     * Deputy constructor.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return Deputy
     */
    public function setId($id): static
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

    /**
     * @param null|string $deputyType
     *
     * @return Deputy
     */
    public function setDeputyType($deputyType): static
    {
        $this->deputyType = $deputyType;
        return $this;
    }

    public function getForename(): string
    {
        return $this->forename;
    }

    /**
     * @param string $forename
     *
     * @return Deputy
     */
    public function setForename($forename): static
    {
        $this->forename = $forename;
        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return Deputy
     */
    public function setSurname($surname): static
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

    /**
     * @param DateTime $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth($dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     *
     * @return Deputy
     */
    public function setEmailAddress($emailAddress): static
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getDaytimeContactNumber(): ?string
    {
        return $this->daytimeContactNumber;
    }

    /**
     * @param string $daytimeContactNumber
     *
     * @return Deputy
     */
    public function setDaytimeContactNumber($daytimeContactNumber): static
    {
        $this->daytimeContactNumber = $daytimeContactNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getEveningContactNumber(): ?string
    {
        return $this->eveningContactNumber;
    }

    /**
     * @param string $eveningContactNumber
     *
     * @return Deputy
     */
    public function setEveningContactNumber($eveningContactNumber): static
    {
        $this->eveningContactNumber = $eveningContactNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobileContactNumber(): ?string
    {
        return $this->mobileContactNumber;
    }

    /**
     * @param string $mobileContactNumber
     *
     * @return Deputy
     */
    public function setMobileContactNumber($mobileContactNumber): static
    {
        $this->mobileContactNumber = $mobileContactNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine1
     *
     * @return Deputy
     */
    public function setAddressLine1($addressLine1): static
    {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine2
     *
     * @return Deputy
     */
    public function setAddressLine2($addressLine2): static
    {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine3(): ?string
    {
        return $this->addressLine3;
    }

    /**
     * @param string $addressLine3
     *
     * @return Deputy
     */
    public function setAddressLine3($addressLine3): static
    {
        $this->addressLine3 = $addressLine3;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressTown(): ?string
    {
        return $this->addressTown;
    }

    /**
     * @param string $addressTown
     *
     * @return Deputy
     */
    public function setAddressTown($addressTown): static
    {
        $this->addressTown = $addressTown;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCounty(): ?string
    {
        return $this->addressCounty;
    }

    /**
     * @param string $addressCounty
     *
     * @return Deputy
     */
    public function setAddressCounty($addressCounty): static
    {
        $this->addressCounty = $addressCounty;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    /**
     * @param string $addressPostcode
     *
     * @return Deputy
     */
    public function setAddressPostcode($addressPostcode): static
    {
        $this->addressPostcode = $addressPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    /**
     * @param string $addressCountry
     *
     * @return Deputy
     */
    public function setAddressCountry($addressCountry): static
    {
        $this->addressCountry = $addressCountry;
        return $this;
    }

    /**
     * @return string
     */
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
