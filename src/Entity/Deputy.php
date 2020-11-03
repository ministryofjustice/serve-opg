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
 * @ORM\Table(name="deputy")
 */
class Deputy
{
    const DEPUTY_TYPE_LAY = 'LAY';
    const DEPUTY_TYPE_PA = 'PUBLIC_AUTHORITY';
    const DEPUTY_TYPE_PROF = 'PROFESSIONAL';

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var string|null see DEPUTY_TYPE_* values
     *
     * @ORM\Column(name="deputy_type", type="string", length=255)
     */
    private $deputyType;

    /**
     * @var string
     *
     * @ORM\Column(name="forename", type="string", length=255)
     */
    private $forename;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255)
     */
    private $surname;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dob", type="date", nullable=true)
     * @Assert\NotBlank(message="deputy.dateOfBirth.notBlank", groups={"deputy-add"})
     */
    private $dateOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.emailAddress.notBlank", groups={"deputy-add"})
     */
    private $emailAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="daytime_contact_number", type="string", length=255, nullable=true)
     */
    private $daytimeContactNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="evening_contact_number", type="string", length=255, nullable=true)
     */
    private $eveningContactNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile_contact_number", type="string", length=255, nullable=true)
     */
    private $mobileContactNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_1", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.address_line_1.notBlank", groups={"deputy-add"})
     */
    private $addressLine1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_2", type="string", length=255, nullable=true)
     */
    private $addressLine2;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line_3", type="string", length=255, nullable=true)
     */
    private $addressLine3;

    /**
     * @var string
     *
     * @ORM\Column(name="address_town", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.address_town.notBlank", groups={"deputy-add"})
     */
    private $addressTown;

    /**
     * @var string
     *
     * @ORM\Column(name="address_county", type="string", length=255, nullable=true)
     */
    private $addressCounty;

    /**
     * @var string
     *
     * @ORM\Column(name="address_postcode", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="deputy.address_postcode.notBlank", groups={"deputy-add"})
     */
    private $addressPostcode;

    /**
     * @var string
     */
    private $addressCountry;

    /**
     * Deputy constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return Deputy
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return Deputy
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }


    /**
     * @return null|string
     */
    public function getDeputyType()
    {
        return $this->deputyType;
    }

    /**
     * @param null|string $deputyType
     *
     * @return Deputy
     */
    public function setDeputyType($deputyType)
    {
        $this->deputyType = $deputyType;
        return $this;
    }

    /**
     * @return string
     */
    public function getForename()
    {
        return $this->forename;
    }

    /**
     * @param string $forename
     *
     * @return Deputy
     */
    public function setForename($forename)
    {
        $this->forename = $forename;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return Deputy
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->forename . ' ' . $this->surname;
    }

    /**
     * @return \DateTime $dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param \DateTime $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     *
     * @return Deputy
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getDaytimeContactNumber()
    {
        return $this->daytimeContactNumber;
    }

    /**
     * @param string $daytimeContactNumber
     *
     * @return Deputy
     */
    public function setDaytimeContactNumber($daytimeContactNumber)
    {
        $this->daytimeContactNumber = $daytimeContactNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getEveningContactNumber()
    {
        return $this->eveningContactNumber;
    }

    /**
     * @param string $eveningContactNumber
     *
     * @return Deputy
     */
    public function setEveningContactNumber($eveningContactNumber)
    {
        $this->eveningContactNumber = $eveningContactNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobileContactNumber()
    {
        return $this->mobileContactNumber;
    }

    /**
     * @param string $mobileContactNumber
     *
     * @return Deputy
     */
    public function setMobileContactNumber($mobileContactNumber)
    {
        $this->mobileContactNumber = $mobileContactNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine1
     *
     * @return Deputy
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine2
     *
     * @return Deputy
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @param string $addressLine3
     *
     * @return Deputy
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressTown()
    {
        return $this->addressTown;
    }

    /**
     * @param string $addressTown
     *
     * @return Deputy
     */
    public function setAddressTown($addressTown)
    {
        $this->addressTown = $addressTown;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCounty()
    {
        return $this->addressCounty;
    }

    /**
     * @param string $addressCounty
     *
     * @return Deputy
     */
    public function setAddressCounty($addressCounty)
    {
        $this->addressCounty = $addressCounty;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    /**
     * @param string $addressPostcode
     *
     * @return Deputy
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @param string $addressCountry
     *
     * @return Deputy
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressFormatted()
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
