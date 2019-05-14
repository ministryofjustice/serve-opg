<?php

namespace AppBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Document
{
    const TYPE_COP1A = 'COP1A'; // required by PF
    const TYPE_COP3 = 'COP3'; // required by PF and HW
    const TYPE_COP4 = 'COP4'; // required by PF and HW
    const TYPE_COURT_ORDER = 'COURT_ORDER'; //required by PF and HW
    const TYPE_ADDITIONAL = 'OTHER'; // not required

    const FILE_NAME_MAX_LENGTH = 255;
    const MAX_UPLOAD_PER_ORDER = 100;
    const MAX_UPLOAD_FILE_SIZE = '20M';

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

    /***
     * @var string
     */
    private $remoteStorageReference;

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
        //$this->setCreatedAt(new \DateTime());
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
     * @return string
     */
    public function getRemoteStorageReference()
    {
        return $this->remoteStorageReference;
    }

    /**
     * @param string $remoteStorageReference
     */
    public function setRemoteStorageReference($remoteStorageReference)
    {
        $this->remoteStorageReference = $remoteStorageReference;
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
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public static function getPermittedMimeTypes()
    {
        return self::permittedMimeTypes;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('file', new Assert\File(array(
            'maxSize' => self::MAX_UPLOAD_FILE_SIZE,
            'mimeTypes' => [
                'application/pdf',
                'application/x-pdf',
                'image/png',
                'image/jpeg',
                'image/tiff',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ],
            'mimeTypesMessage' => 'document.file.errors.mimeTypesMessage',
            'maxSizeMessage' => 'document.file.errors.maxSizeMessage'
        )));
    }
}
