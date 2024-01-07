<?php

namespace App\Entity;

use App\exceptions\NoMatchesFoundException;
use App\exceptions\WrongCaseNumberException;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "PF" = "App\Entity\OrderPf",
 *     "HW" = "App\Entity\OrderHw",
 * })
 * @ORM\Table(name="dc_order")
 */
abstract class Order
{
    const TYPE_PF = 'PF';
    const TYPE_HW = 'HW';
    const TYPE_BOTH = 'both';

    const SUBTYPE_NEW = 'NEW_APPLICATION';
    const SUBTYPE_REPLACEMENT = 'REPLACEMENT_OF_DISCHARGED_DEPUTY';
    const SUBTYPE_INTERIM_ORDER = 'INTERIM_ORDER';

    const HAS_ASSETS_ABOVE_THRESHOLD_YES = 'yes';
    const HAS_ASSETS_ABOVE_THRESHOLD_NO = 'no';

    const HAS_ASSETS_ABOVE_THRESHOLD_NA = 'na';

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
    abstract public function isOrderValid();

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="orders", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Client $client;

    /**
     * @ORM\Column(name="sub_type", type="string", length=50, nullable=true)
     */
    private ?string $subType = null;

    /**
     * @ORM\Column(name="has_assets_above_threshold", type="string", length=50, nullable=true)
     */
    private ?string $hasAssetsAboveThreshold = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Deputy", cascade={"persist"})
     * @ORM\JoinTable(name="ordertype_deputy",
     *   joinColumns={@ORM\JoinColumn(name="deputy_id", referencedColumnName="id", onDelete="CASCADE")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private Collection $deputies;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="order", cascade={"persist"})
     */
    private Collection $documents;

    /**
     * @ORM\Column(name="appointment_type", type="string", length=50, nullable=true)
     */
    private ?string $appointmentType = null;

    /**
     * Date order was created in DC database
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * Date order was first made outside DC
     *
     * @ORM\Column(name="made_at", type="datetime", options={"default":"2017-01-01 00:00:00"})
     */
    private \DateTime $madeAt;

    /**
     * @ORM\Column(name="issued_at", type="datetime", nullable=true)
     */
    private \DateTime $issuedAt;

    /**
     * @ORM\Column(name="served_at", type="datetime", nullable=true)
     */
    private ?\DateTime $servedAt = null;

    /**
     * JSON string served to the API
     *
     * @ORM\Column(name="payload_served", type="json_array", nullable=true)
     */
    private ?array $payloadServed;

    /**
     * API response as a string
     *
     * @ORM\Column(name="api_response", type="json_array", nullable=true)
     */
    private ?array $apiResponse;

    /**
     * @ORM\Column(name="order_number", type="string", nullable=true, unique=true)
     */
    private ?string $orderNumber = null;

    /**
     * Order constructor.
     *
     * @param \DateTime $madeAt      Date Order was first made, outside DC
     * @param \DateTime $issuedAt    Date Order was issues at
     * @param string    $orderNumber The order number from casrec
     *
     * @throws \Exception
     */
    public function __construct(
        Client $client,
        \DateTime $madeAt,
        \DateTime $issuedAt,
        string $orderNumber,
        string $createdAt = 'now'
    ) {
        $this->client = $client;
        $this->madeAt = $madeAt;
        $this->issuedAt = $issuedAt;
        $this->orderNumber = $orderNumber;

        $this->createdAt = new \DateTime($createdAt);
        $this->deputies = new ArrayCollection();
        $this->documents = new ArrayCollection();

        $client->addOrder($this);
    }

    /**
    /**
     * @return bool
     */
    public function readyToServe(): bool
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

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): Order
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    /**
     * Has at least one deputy by type
     *
     * @param $deputyType
     * @return int|void
     */
    protected function hasDeputyByType($deputyType)
    {
        return $this->getDeputiesByType($deputyType)->count();
    }

    /**
     * Returns a list of deputies by type
     *
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection|static
     */
    public function getDeputiesByType($deputyType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('deputyType', $deputyType));

        return $this->getDeputies()->matching($criteria);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param null|int $id
     */
    public function setId($id): Order
    {
        $this->id = $id;
        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): Order
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    abstract public function getType();

    public function getSubType(): ?string
    {
        return $this->subType;
    }

    public function setSubType(?string $subType): Order
    {
        $this->subType = $subType;
        return $this;
    }

    public function getHasAssetsAboveThreshold(): ?string
    {
        return $this->hasAssetsAboveThreshold;
    }

    public function setHasAssetsAboveThreshold(?string $hasAssetsAboveThreshold): Order
    {
        $this->hasAssetsAboveThreshold = $hasAssetsAboveThreshold;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAppointmentType(): ?string
    {
        return $this->appointmentType;
    }

    /**
     * @param null|string $appointmentType
     *
     * @return Order
     */
    public function setAppointmentType($appointmentType): static
    {
        $this->appointmentType = $appointmentType;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getMadeAt(): \DateTime
    {
        return $this->madeAt;
    }

    public function getIssuedAt(): \DateTime
    {
        return $this->issuedAt;
    }


    /**
     * @return ArrayCollection
     */
    public function getDeputies(): Collection
    {
        return $this->deputies;
    }

    /**
     * @param ArrayCollection $deputies
     */
    public function setDeputies($deputies): void
    {
        $this->deputies = $deputies;
    }

    public function addDeputy(Deputy $deputy): void
    {
        if (!$this->deputies->contains($deputy)) {
            $this->deputies->add($deputy);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocumentsByType($type)
    {
        return $this->documents->filter(function ($doc) use ($type): bool {
            return $doc->getType() == $type;
        });
    }

    public function setDocuments(ArrayCollection $documents): void
    {
        $this->documents = $documents;
    }

    public function setServedAt(\DateTime $servedAt = null): Order
    {
        $this->servedAt = $servedAt;
        return $this;
    }

    public function getServedAt(): ?\DateTime
    {
        return $this->servedAt;
    }

    /**
     * Filter o ut a deputy from the list of deputies assigned to this order
     *
     * @return bool|static
     */
    public function getDeputyById($deputyId)
    {
        $result = $this->getDeputies()->filter(
            function (Deputy $deputy) use ($deputyId): bool {
                return $deputy->getId() == $deputyId;
            }
        );
        return $result->count() > 0 ? $result->first() : null;
    }

    /**
     * Remove a deputy from the order
     *
     * @return $this
     */
    public function removeDeputy(Deputy $deputy): static
    {
        if (!$this->deputies->contains($deputy)) {
            $this->deputies->removeElement($deputy);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPayloadServed(): ?array
    {
        return $this->payloadServed;
    }

    /**
     * @param string $payloadServed
     *
     * @return $this
     */
    public function setPayloadServed($payloadServed): static
    {
        $this->payloadServed = $payloadServed;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiResponse(): ?array
    {
        return $this->apiResponse;
    }

    /**
     * @param string $apiResponse
     *
     * @return $this
     */
    public function setApiResponse($apiResponse): static
    {
        $this->apiResponse = $apiResponse;
        return $this;
    }
}
