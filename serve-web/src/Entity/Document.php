<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

#[ORM\Table(name: 'document')]
#[ORM\Entity]
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

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Order', inversedBy: 'documents', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Order $order = null;

    #[ORM\Column(name: 'type', type: 'string', length: 100)]
    private ?string $type = null;

    private ?UploadedFile $file = null;

    #[ORM\Column(name: 'filename', type: 'string', length: 255, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(name: 'storagereference', type: 'string', length: 255)]
    private string $storageReference;

    #[ORM\Column(name: 'remotestoragereference', type: 'string', length: 255, nullable: true)]
    private ?string $remoteStorageReference;

    private DateTime $createdAt;

    public function __construct(?Order $order, ?string $type)
    {
        $this->order = $order;
        $this->type = $type;
        //$this->setCreatedAt(new \DateTime());
    }

    public function isValidForOrder(ExecutionContextInterface $context): void
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Document
    {
        $this->id = $id;
        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): Document
    {
        $this->order = $order;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Document
    {
        $this->type = $type;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getStorageReference(): string
    {
        return $this->storageReference;
    }

    public function setStorageReference(string $storageReference): static
    {
        $this->storageReference = $storageReference;

        return $this;
    }

    public function getRemoteStorageReference(): ?string
    {
        return $this->remoteStorageReference;
    }

    public function setRemoteStorageReference(?string $remoteStorageReference): void
    {
        $this->remoteStorageReference = $remoteStorageReference;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @deprecated
     */
    public static function getPermittedMimeTypes()
    {
        return self::permittedMimeTypes;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
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

    public function isWordDocument(): bool
    {
        $wordMimeTypes = ['application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        return in_array($this->getMimeType(), $wordMimeTypes);
    }

    public function getMimeType(): ?string
    {
        if (!$this->getFile()) {
            return null;
        }

        return $this->getFile()->getClientMimeType();
    }
}
