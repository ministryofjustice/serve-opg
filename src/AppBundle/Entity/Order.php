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
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Order constructor.
     * @param Client $client
     * @param string $type
     * @param string $subType
     * @param string $hasAssetsAboveThreshold
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->createdAt = new \DateTime();
        $this->deputies = new ArrayCollection();

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
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
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

}
