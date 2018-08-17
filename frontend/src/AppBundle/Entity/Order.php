<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

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
    private $deputys;

    /**
     * @var \DateTime
     */
    private $createdAt;

//    /**
//     * @param Client $client
//     * @param $type TYPE_*
//     *
//     * @return OrderHw|OrderPa
//     */
//    public static function factory(Client $client, $type)
//    {
//        switch($type) {
//            case self::TYPE_PA:
//                return new OrderPa($client);
//            case self::TYPE_HW:
//                return new OrderHw($client);
//        }
//        throw new \InvalidArgumentException("Unrecognised type $type");
//    }

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
        $this->deputys = new ArrayCollection();

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
     * @return string
     */
    abstract public function getType();

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
        if (in_array($orderType, [Order::TYPE_HW, Order::TYPE_PA])) {
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
    public function getDeputys()
    {
        return $this->deputys;
    }

    /**
     * @param ArrayCollection $deputys
     */
    public function setDeputys($deputys)
    {
        $this->deputys = $deputys;
    }

    /**
     * @param Deputy $deputy
     */
    public function addDeputy(Deputy $deputy)
    {
        if (!$this->deputys->contains($deputy)) {
            $this->deputys->add($deputy);
        }
    }

}
