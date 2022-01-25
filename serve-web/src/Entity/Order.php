<?php

namespace App\Entity;

use App\exceptions\NoMatchesFoundException;
use App\exceptions\WrongCaseNumberException;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="orders", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @var string|null see SUBTYPE_* values
     *
     * @ORM\Column(name="sub_type", type="string", length=50, nullable=true)
     */
    private $subType;

    /**
     * @var string|null yes/no/na/null
     *
     * @ORM\Column(name="has_assets_above_threshold", type="string", length=50, nullable=true)
     */
    private $hasAssetsAboveThreshold;

    /**
     * @var ArrayCollection of Deputy[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Deputy", cascade={"persist"})
     * @ORM\JoinTable(name="ordertype_deputy",
     *   joinColumns={@ORM\JoinColumn(name="deputy_id", referencedColumnName="id", onDelete="CASCADE")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="order_type_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $deputies;

    /**
     * @var ArrayCollection of Document[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="order", cascade={"persist"})
     */
    private $documents;

    /**
     * @var string|null see APPOINTMENT_TYPE_* values
     *
     * @ORM\Column(name="appointment_type", type="string", length=50, nullable=true)
     */
    private $appointmentType;

    /**
     * Date order was created in DC database
     *
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * Date order was first made outside DC
     *
     * @var DateTime
     *
     * @ORM\Column(name="made_at", type="datetime", options={"default":"2017-01-01 00:00:00"})
     */
    private $madeAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="issued_at", type="datetime", nullable=true)
     */
    private $issuedAt;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="served_at", type="datetime", nullable=true)
     */
    private $servedAt;

    /**
     * JSON string served to the API
     *
     * @var string
     *
     * @ORM\Column(name="payload_served", type="json_array", nullable=true)
     */
    private $payloadServed;

    /**
     * API response as a string
     *
     * @var string
     *
     * @ORM\Column(name="api_response", type="json_array", nullable=true)
     */
    private $apiResponse;

    /**
     * @var string|null
     *
     * @ORM\Column(name="order_number", type="string", nullable=true)
     */
    private $orderNumber;

    /**
     * Order constructor.
     * @param Client $client
     * @param DateTime $madeAt Date Order was first made, outside DC
     * @param DateTime $issuedAt Date Order was issues at
     * @param string $orderNumber The order number from casrec
     * @param string $createdAt
     *
     * @throws Exception
     */
    public function __construct(
        Client $client,
        DateTime $madeAt,
        DateTime $issuedAt,
        string $orderNumber,
        string $createdAt = 'now')
    {
        $this->client = $client;
        $this->madeAt = $madeAt;
        $this->issuedAt = $issuedAt;
        $this->orderNumber = $orderNumber;

        $this->createdAt = new DateTime($createdAt);
        $this->deputies = new ArrayCollection();
        $this->documents = new ArrayCollection();

        $client->addOrder($this);
    }

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

    /**
     * @return string|null
     */
    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    /**
     * @param string|null $orderNumber
     * @return Order
     */
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
     * @param $deputyType
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection|static
     */
    public function getDeputiesByType($deputyType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('deputyType', $deputyType));

        return $this->getDeputies()->matching($criteria);
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
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime
     */
    public function getMadeAt(): DateTime
    {
        return $this->madeAt;
    }

    /**
     * @return DateTime
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
        return $this->documents->filter(function ($doc) use ($type) {
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
     * @param DateTime|null $servedAt
     * @return Order
     */
    public function setServedAt(DateTime $servedAt = null): Order
    {
        $this->servedAt = $servedAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getServedAt(): ?DateTime
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
            function (Deputy $deputy) use ($deputyId) {
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

    /**
     * @return string
     */
    public function getPayloadServed()
    {
        return $this->payloadServed;
    }

    /**
     * @param string $payloadServed
     *
     * @return $this
     */
    public function setPayloadServed($payloadServed)
    {
        $this->payloadServed = $payloadServed;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiResponse()
    {
        return $this->apiResponse;
    }

    /**
     * @param string $apiResponse
     *
     * @return $this
     */
    public function setApiResponse($apiResponse)
    {
        $this->apiResponse = $apiResponse;
        return $this;
    }
}
