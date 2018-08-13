<?php
namespace AppBundle\Entity;

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

    /**
     * @var int
     */
    private $id;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string see TYPE_* values
     */
    private $type;

    /**
     * @var string see SUBTYPE_* values
     */
    private $subType;

    /**
     * @var string yes/no/na/null
     */
    private $hasAssetsAboveThreshold;

    /**
     * Order constructor.
     * @param Client $client
     * @param string $type
     * @param string $subType
     * @param string $hasAssetsAboveThreshold
     */
    public function __construct(Client $client, string $type, string $subType, string $hasAssetsAboveThreshold)
    {
        $this->client = $client;
        $this->type = $type;
        $this->subType = $subType;
        $this->hasAssetsAboveThreshold = hasAssetsAboveThreshold;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSubType(): string
    {
        return $this->subType;
    }

    /**
     * @return string
     */
    public function hasAssetsAboveThreshold(): string
    {
        return $this->hasAssetsAboveThreshold;
    }


}