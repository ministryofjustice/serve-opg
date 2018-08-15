<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class Order
{
    const TYPE_PROPERTY_AFFAIRS = 'pa';
    const TYPE_HEALTH_WELFARE = 'hw';
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
     * @var Collection
     */
    private $types;

    /**
     * @var string|null see SUBTYPE_* values
     */
    private $subType;

    /**
     * @var string|null yes/no/na/null
     */
    private $hasAssetsAboveThreshold;

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
        $this->types = new ArrayCollection();

        $client->addOrder($this);

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
     * @return Order
     */
    public function setId(?int $id): Order
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
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     * @return Order
     */
    public function setType(?string $type): Order
    {
        $this->type = $type;
        return $this;
    }

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
     * @param OrderType $order
     */
    public function addType(OrderType $type)
    {
        if (!$this->types->contains($type)) {
            $type->setOrder($this);
            $this->types->add($type);
        }
    }

    /**
     * @return Collection
     */
    public function getTypes(): Collection
    {
        return $this->types;
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
     * @return ArrayCollection
     */
    public function getAllDeputys()
    {
        $deputies = new ArrayCollection();
        foreach ($this->getTypes() as $ot) {
            foreach ($ot->getDeputys() as $dep) {
                $deputies->add($dep);
            }
        }
        return $deputies;
    }

    /**
     * Return a specific orderType from the order based on type ('hw' or 'pa')
     *
     * @param null $type
     * @return null
     */
    public function getTypesByOrderType($orderType = null)
    {
        if (in_array($orderType, [Order::TYPE_HEALTH_WELFARE, Order::TYPE_PROPERTY_AFFAIRS])) {
            $orderTypes = $this->getTypes();

            // declare a class name to search the array for
            $objectClass = 'AppBundle\Entity\OrderType' . ucfirst($orderType);
            foreach ($orderTypes->toArray() as $ot) {
                if ($ot instanceof $objectClass) {
                    return $ot;
                }
            }
        }
        return null;
    }


}
