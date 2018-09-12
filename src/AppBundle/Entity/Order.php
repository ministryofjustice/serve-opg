<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Order
{
    const TYPE_PA = 'pa';
    const TYPE_HW = 'hw';
    const TYPE_BOTH = 'both';

    const SUBTYPE_NEW = 'new';
    const SUBTYPE_REPLACEMENT = 'replacement';
    const SUBTYPE_INTERIM_ORDER = 'interim-order';
    const SUBTYPE_TRUSTEE = 'trustee';
    const SUBTYPE_VARIATION = 'variation';
    const SUBTYPE_DIRECTION = 'direction';

    const HAS_ASSETS_YES = 'yes';
    const HAS_ASSETS_NO = 'no';
    const HAS_ASSETS_NA = 'na';

    const APPOINTMENT_TYPE_SOLE = 'sole';
    const APPOINTMENT_TYPE_JOINT = 'joint';
    const APPOINTMENT_TYPE_JOINT_AND_SEVERAL = 'js';

    public static function getExpectedDocuments()
    {
        return [
            Document::TYPE_COP1A,
            Document::TYPE_COP1C,
            Document::TYPE_COP3,
            Document::TYPE_COP4,
            Document::TYPE_COURT_ORDER,
        ];
    }

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string|null see TYPE_* values
     */
    private $type;

    /**
     * @var string|null see SUBTYPE_* values
     */
    private $subType;

    /**
     * @var string|null yes/no/na/null
     */
    private $hasAssetsAboveThreshold;

    /**
     * @var ArrayCollection of Deputy[]
     */
    private $deputies;

    /**
     * @var ArrayCollection of Document[]
     */
    private $documents;

    /**
     * @var string|null see APPOINTMENT_TYPE_* values
     */
    private $appointmentType;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $issuedAt;

    /**
     * @var \DateTime|null
     */
    private $servedAt;

    /**
     * @param Client $client
     * @param \DateTime $issuedAt
     */
    public function __construct(Client $client, \DateTime $issuedAt)
    {
        $this->client = $client;
        $this->issuedAt =$issuedAt;

        $this->createdAt = new \DateTime();
        $this->deputies = new ArrayCollection();
        $this->documents = new ArrayCollection();

        $client->addOrder($this);

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
     * @return Order
     */
    public function setId($id): Order
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return Order
     */
    public function setClient(Client $client): Order
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    abstract public function getType();


    /**
     * @return null|string
     */
    public function getSubType(): ?string
    {
        return $this->subType;
    }

    /**
     * @param null|string $subType
     * @return Order
     */
    public function setSubType(?string $subType): Order
    {
        $this->subType = $subType;
        return $this;
    }


    /**
     * @return null|string
     */
    public function getHasAssetsAboveThreshold(): ?string
    {
        return $this->hasAssetsAboveThreshold;
    }

    /**
     * @param null|string $hasAssetsAboveThreshold
     * @return Order
     */
    public function setHasAssetsAboveThreshold(?string $hasAssetsAboveThreshold): Order
    {
        $this->hasAssetsAboveThreshold = $hasAssetsAboveThreshold;
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
     * @return Order
     */
    public function setAppointmentType($appointmentType)
    {
        $this->appointmentType = $appointmentType;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getIssuedAt()
    {
        return $this->issuedAt;
    }


    /**
     * @return ArrayCollection
     */
    public function getDeputies()
    {
        return $this->deputies;
    }

    /**
     * @param ArrayCollection $deputies
     */
    public function setDeputies($deputies)
    {
        $this->deputies = $deputies;
    }

    /**
     * @param Deputy $deputy
     */
    public function addDeputy(Deputy $deputy)
    {
        if (!$this->deputies->contains($deputy)) {
            $this->deputies->add($deputy);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocumentsByType($type)
    {
        return $this->documents->filter(function($doc) use ($type) {
            return $doc->getType() == $type;
        });
    }

    /**
     * @param ArrayCollection $documents
     */
    public function setDocuments(ArrayCollection $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @param \DateTime|null $servedAt
     * @return Order
     */
    public function setServedAt(\DateTime $servedAt = null): Order
    {
        $this->servedAt = $servedAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getServedAt(): ?\DateTime
    {
        return $this->servedAt;
    }

    /**
     * Return true if the order has
     * - at least one deputy
     * - a document for each `getExpectedDocuments()`
     * - `hasAssetsAboveThreshold` (PA oly), `subType` and `appointmentType` answered
     *
     * @return bool
     */
    public function readyToServe()
    {
        if (!count($this->getDeputies())) {
            return false;
        }

        foreach(self::getExpectedDocuments() as $type) {
            if (!count($this->getDocumentsByType($type))) {
                return false;
            }
        }

        if ($this instanceof OrderPa && empty($this->getHasAssetsAboveThreshold())) {
            return false;
        }

        if (empty($this->getSubType())) {
            return false;
        }

        if (empty($this->getAppointmentType())) {
            return false;
        }

        return true;
    }

}
