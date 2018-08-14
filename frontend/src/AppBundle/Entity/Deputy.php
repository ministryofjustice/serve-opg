<?php

namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class Deputy
{

    const APPOINTMENT_TYPE_SOLE = 'sole';
    const APPOINTMENT_TYPE_JOINT = 'joint';
    const APPOINTMENT_TYPE_JOINT_AND_SEVERAL = 'joint-several';

    const DEPUTY_TYPE_LAY = 'lay';
    const DEPUTY_TYPE_PA = 'pa';
    const DEPUTY_TYPE_PROF = 'prof';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var string|null see APPOINTMENT_TYPE_* values
     */
    private $appointmentType;

    /**
     * @var string|null see DEPUTY_TYPE_* values
     */
    private $deputyType;

    /**
     * @var string
     */
    private $forename;

    /**
     * @var string
     */
    private $surname;

    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var string
     */
    private $contactNumber;

    /**
     * @var string
     */
    private $addressLine1;

    /**
     * @var string
     */
    private $addressLine2;

    /**
     * @var string
     */
    private $addressLine3;

    /**
     * @var string
     */
    private $addressTown;

    /**
     * @var string
     */
    private $addressCounty;

    /**
     * @var string
     */
    private $addressPostcode;

    /**
     * @var string
     */
    private $addressCountry;

    /**
     * Deputy constructor.
     *
     * @param Order $order
     * @param string $appointmentType
     * @param string $deputyType
     * @param array $personalDetails
     * @param array $addressDetails
     */
    public function __construct(Order $order, $appointmentType, $deputyType, $personalDetails, $addressDetails)
    {
        $this->order = $order;
        $this->appointmentType = $appointmentType;
        $this->deputyType = $deputyType;
        $this->constructPersonalDetails($personalDetails);
        $this->constructAddressDetails($addressDetails);
    }

    /**
     * Assign personal details to deputy object
     *
     * @param $personalDetails
     */
    private function constructPersonalDetails($personalDetails) {
        $this->forename = $personalDetails['forename'];
        $this->surname = $personalDetails['surname'];
        $this->emailAddress = $personalDetails['emailAddress'];
        $this->contactNumber = $personalDetails['contactNumber'];
    }

    /**
     * Assign address details to deputy object
     *
     * @param $addressDetails
     */
    private function constructAddressDetails($addressDetails) {
        $this->addressLine1 = $addressDetails['addressLine1'];
        $this->addressLine2 = $addressDetails['addressLine2'];
        $this->addressLine3 = $addressDetails['addressLine3'];
        $this->addressTown = $addressDetails['addressTown'];
        $this->addressCounty = $addressDetails['addressCounty'];
        $this->addressPostcode = $addressDetails['addressPostcode'];
        $this->addressCountry = $addressDetails['addressCountry'];
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Deputy
     */
    public function setId(?int $id): Order
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return Deputy
     */
    public function setOrder(Order $order): Order
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAppointmentType(): ? string
    {
        return $this->appointmentType;
    }

    /**
     * @param null|string $appointmentType
     *
     * @return Deputy
     */
    public function setAppointmentType(?string $appointmentType): string
    {
        $this->appointmentType = $appointmentType;
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
     */
    public function setDeputyType($deputyType)
    {
        $this->deputyType = $deputyType;
    }





}
