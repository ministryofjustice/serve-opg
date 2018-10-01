<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Document
{
        const TYPE_COP1A = 'COP1A'; // required by PF
    const TYPE_COP1C = 'COP1C'; // displayed by PF, but not required
    const TYPE_COP3 = 'COP3'; // required by PF and HW
    const TYPE_COP4 = 'COP4'; // required by PF and HW
    const TYPE_COURT_ORDER = 'COURT_ORDER'; //required by PF and HW

    const TYPE_ADDITIONAL = 'OTHER'; // not required

    const FILE_NAME_MAX_LENGTH = 255;
    const MAX_UPLOAD_PER_ORDER = 100;

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

//    /**
//     * // add more validators here if needed
//     * http://symfony.com/doc/current/reference/constraints/File.html
//     *
//     * @Assert\NotBlank(message="Please choose a file", groups={"document"})
//     * @Assert\File(
//     *     maxSize = "15M",
//     *     maxSizeMessage = "document.file.errors.maxSizeMessage",
//     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/png", "image/jpeg"},
//     *     mimeTypesMessage = "document.file.errors.mimeTypesMessage",
//     *     groups={"document"}
//     * )

     /**
     *
     * @var UploadedFile
     */
    private $file;

    /**
     * @var string
     */
    private $fileName;

    /***
     * @var string
     */
    private $storageReference;

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
     * @param ExecutionContextInterface $context
     */
    public function isValidForOrder(ExecutionContextInterface $context)
    {
        if (!($this->getFile() instanceof UploadedFile)) {
            return;
        }

        $fileNames = [];
        foreach ($this->getOrder()->getDocuments() as $document) {
            $fileNames[] = $document->getFileName();
        }

        $fileOriginalName = $this->getFile()->getClientOriginalName();

        if (strlen($fileOriginalName) > self::FILE_NAME_MAX_LENGTH) {
            $context->buildViolation('document.file.errors.maxMessage')->atPath('file')->addViolation();
            return;
        }

        if (in_array($fileOriginalName, $fileNames)) {
            $context->buildViolation('document.file.errors.alreadyPresent')->atPath('file')->addViolation();
            return;
        }

//        if (count($this->getReport()->getDocuments()) >= self::MAX_UPLOAD_PER_ORDER) {
//            $context->buildViolation('document.file.errors.maxDocumentsPerReport')->atPath('file')->addViolation();
//            return;
//        }
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
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param  string   $fileName
     * @return Document
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStorageReference()
    {
        return $this->storageReference;
    }

    /**
     * @param  string   $storageReference
     * @return Document
     */
    public function setStorageReference($storageReference)
    {
        $this->storageReference = $storageReference;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

}
