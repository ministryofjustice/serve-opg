<?php

namespace AppBundle\Entity;

class Document
{
    const TYPE_COP1A = 'cop1a';
    const TYPE_COP1C = 'cop1c';
    const TYPE_COP3 = 'cop3';
    const TYPE_COP4 = 'cop3';
    const TYPE_COURT_ORDER = 'co';
    const TYPE_OTHER = 'co';
    const TYPE_ADDITIONAL = 'additional';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Order|null
     */
    private $order;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $file;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Document constructor.
     * @param Order|null $order
     * @param null|string $type
     */
    public function __construct(Order $order, string $type)
    {
        $this->order = $order;
        $this->type = $type;
        $this->createdAt = new \DateTime();
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
     * @return Document
     */
    public function setId(?int $id): Document
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order|null $order
     * @return Document
     */
    public function setOrder(?Order $order): Document
    {
        $this->order = $order;
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
     * @return Document
     */
    public function setType(?string $type): Document
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * @param null|string $file
     * @return Document
     */
    public function setFile(?string $file): Document
    {
        $this->file = $file;
        return $this;
    }



}
