<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Order
{
    const TYPE_PA = 'PF';
    const TYPE_HW = 'HW ';
    const TYPE_BOTH = 'both';

    const SUBTYPE_NEW = 'NEW_APPLICATION';
    const SUBTYPE_REPLACEMENT = 'REPLACEMENT_OF_DISCHARGED_DEPUTY';
    const SUBTYPE_INTERIM_ORDER = 'INTERIM_ORDER';
    const SUBTYPE_TRUSTEE = 'TRUSTEE';
    const SUBTYPE_VARIATION = 'VARIATION';
    const SUBTYPE_DIRECTION = 'DIRECTION';

    const HAS_ASSETS_YES = 'yes';
    const HAS_ASSETS_NO = 'no';
    const HAS_ASSETS_NA = 'na';

    const APPOINTMENT_TYPE_SOLE = 'SOLE';
    const APPOINTMENT_TYPE_JOINT = 'JOINT';
    const APPOINTMENT_TYPE_JOINT_AND_SEVERAL = 'JOINT_AND_SEVERAL';

    /**
     * @return array
     */
    abstract public function getAcceptedDocumentTypes();

    /**
     * @return boolean
     */
    abstract protected function isOrderValid();

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
     * @return bool
     */
    public function readyToServe()
    {
        if (!$this->isOrderValid() ||
            !count($this->getDeputies())
        ) {
            return false;
        }

        foreach ($this->getAcceptedDocumentTypes() as $type => $required) {
            if ($required && count($this->getDocumentsByType($type)) === 0) {
                return false;
            }
        }

        return true;
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
     * Filter o ut a deputy from the list of deputies assigned to this order
     *
     * @param $deputyId
     * @return bool|static
     */
    public function getDeputyById($deputyId)
    {
        $result = $this->getDeputies()->filter(
            function(Deputy $deputy) use ($deputyId) {
                return $deputy->getId() == $deputyId;
            }
        );
        return $result->count() > 0 ? $result->first() : null;
    }

    /**
     * Remove a deputy from the order
     *
     * @param Deputy $deputy
     * @return $this
     */
    public function removeDeputy(Deputy $deputy)
    {
        if (!$this->deputies->contains($deputy)) {
            $this->deputies->removeElement($deputy);
        }
        return $this;
    }
}
