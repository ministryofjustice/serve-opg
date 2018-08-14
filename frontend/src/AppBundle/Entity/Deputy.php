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
     * @var string|null
     */
    private $orderType;

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
     * @var string
     */
    private $deputyAnswerQ2_6;

    /**
     * @var string
     */
    private $deputyS4ResponsesAnsweredNo;

    /**
     * Deputy constructor.
     * @param Order $order
     * @param null $orderType
     * @param null $appointmentType
     * @param null $deputyType
     * @param array $personalDetails
     * @param array $addressDetails
     * @param null $deputyAnswerQ2_6
     * @param null $deputyS4ResponsesAnsweredNo
     */
    public function __construct(
        Order $order,
        $orderType = null,
        $appointmentType = null,
        $deputyType = null,
        $personalDetails = [],
        $addressDetails = [],
        $deputyAnswerQ2_6 = null,
        $deputyS4ResponsesAnsweredNo = null
    ) {
        $this->order = $order;
        $this->orderType = $orderType;
        $this->appointmentType = $appointmentType;
        $this->deputyType = $deputyType;
        $this->constructPersonalDetails($personalDetails);
        $this->constructAddressDetails($addressDetails);
        $this->deputyAnswerQ2_6 = $deputyAnswerQ2_6;
        $this->deputyS4ResponsesAnsweredNo = $deputyS4ResponsesAnsweredNo;

    }

    /**
     * Assign personal details to deputy object
     *
     * @param $personalDetails
     */
    private function constructPersonalDetails($personalDetails) {
        if (!empty($personalDetails)) {
            $this->forename = $personalDetails['forename'];
            $this->surname = $personalDetails['surname'];
            $this->emailAddress = $personalDetails['emailAddress'];
            $this->contactNumber = $personalDetails['contactNumber'];
        }
    }

    /**
     * Assign address details to deputy object
     *
     * @param $addressDetails
     */
    private function constructAddressDetails($addressDetails) {
        if (!empty($addressDetails)) {
            $this->addressLine1 = $addressDetails['addressLine1'];
            $this->addressLine2 = $addressDetails['addressLine2'];
            $this->addressLine3 = $addressDetails['addressLine3'];
            $this->addressTown = $addressDetails['addressTown'];
            $this->addressCounty = $addressDetails['addressCounty'];
            $this->addressPostcode = $addressDetails['addressPostcode'];
            $this->addressCountry = $addressDetails['addressCountry'];
        }
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
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @param null|string $orderType
     *
     * @return $this
     */
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAppointmentType()
    {
        return $this->appointmentType;
    }

    /**
     * @param null|string $appointmentType
     *
     * @return Deputy
     */
    public function setAppointmentType($appointmentType)
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

    /**
     * @return string
     */
    public function getForename()
    {
        return $this->forename;
    }

    /**
     * @param string $forename
     */
    public function setForename($forename)
    {
        $this->forename = $forename;
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
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->forename . ' ' . $this->surname;
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
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getContactNumber()
    {
        return $this->contactNumber;
    }

    /**
     * @param string $contactNumber
     */
    public function setContactNumber($contactNumber)
    {
        $this->contactNumber = $contactNumber;
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
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;
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
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;
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
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;
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
     */
    public function setAddressTown($addressTown)
    {
        $this->addressTown = $addressTown;
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
     */
    public function setAddressCounty($addressCounty)
    {
        $this->addressCounty = $addressCounty;
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
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
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
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    /**
     * @return string
     */
    public function getAddressFormatted()
    {
        return implode(', ', [
            $this->getAddressLine1(),
            $this->getAddressLine2(),
            $this->getAddressLine3(),
            $this->getAddressCounty(),
            $this->getAddressPostcode(),
            $this->getAddressCountry()
        ]);
    }

    /**
     * @return string
     */
    public function getDeputyAnswerQ26()
    {
        return $this->deputyAnswerQ2_6;
    }

    /**
     * @param string $deputyAnswerQ2_6
     *
     * @return $this
     */
    public function setDeputyAnswerQ26($deputyAnswerQ2_6)
    {
        $this->deputyAnswerQ2_6 = $deputyAnswerQ2_6;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyS4ResponsesAnsweredNo()
    {
        return $this->deputyS4ResponsesAnsweredNo;
    }

    /**
     * @param string $deputyS4ResponsesAnsweredNo
     *
     * @return $this
     */
    public function setDeputyS4ResponsesAnsweredNo($deputyS4ResponsesAnsweredNo)
    {
        $this->deputyS4ResponsesAnsweredNo = $deputyS4ResponsesAnsweredNo;
        return $this;
    }





}
