<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\InvalidFileTypeException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Types\Doc;
use AppBundle\Service\File\Types\Jpg;
use AppBundle\Service\File\Types\Pdf;
use AppBundle\Service\File\Types\Png;
use AppBundle\Service\File\Types\Tif;
use AppBundle\Service\File\Types\UploadableFile;
use AppBundle\Service\File\Types\UploadableFileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileCheckerFactory
{
    /**
     * @var Pdf
     */
    protected $pdf;

    /**
     * @var Png
     */
    protected $png;

    /**
     * @var Jpg
     */
    protected $jpg;

    /**
     * @var Tif
     */
    protected $tif;

    /**
     * @var Doc
     */
    protected $doc;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * FileCheckerFactory constructor.
     * @param Pdf $pdf
     * @param Png $png
     * @param Jpg $jpg
     * @param Tif $tif
     * @param Doc $doc
     * @param LoggerInterface $logger
     */
    public function __construct(
        Pdf $pdf,
        Png $png,
        Jpg $jpg,
        Tif $tif,
        Doc $doc,
        LoggerInterface $logger
    ) {
        $this->pdf = $pdf;
        $this->png = $png;
        $this->jpg = $jpg;
        $this->tif = $tif;
        $this->doc = $doc;
        $this->logger = $logger;
    }

    /**
     * Sets the uploaded file to the file Object created based on mime type
     *
     * @param UploadedFile $uploadedFile
     *
     * @return UploadableFile
     */
    public function factory(UploadedFile $uploadedFile)
    {
        if ($uploadedFile->isExecutable() ||
            preg_match('/(\.exe)(\.bin)(\.bat)(\.jss)(\.zip)(\.php)/i', $uploadedFile->getClientOriginalName())) {
            throw new InvalidFileTypeException(s);
        }
        $mimeType = $uploadedFile->getMimeType();

        switch (true) {
            case ($mimeType == 'application/pdf'):
                return $this->pdf->setUploadedFile($uploadedFile);
            case ($mimeType == 'application/msword' || $this->isWordDoc($uploadedFile)):
                return $this->doc->setUploadedFile($uploadedFile);
            case ($mimeType == 'image/png'):
                return  $this->png->setUploadedFile($uploadedFile);
            case ($mimeType == 'image/jpeg'):
                return $this->jpg->setUploadedFile($uploadedFile);
            case ($mimeType == 'image/tiff'):
                return $this->tif->setUploadedFile($uploadedFile);
            default:
                throw new InvalidFileTypeException();
        }
    }

    /**
     * Is file a word docx file?
     *
     * @param UploadedFile $uploadedFile
     * @return bool
     */
    private function isWordDoc(UploadedFile $uploadedFile)
    {
        // specific check to handle word X documents but not other files with same mime type
        $mimeType = $uploadedFile->getMimeType();

        if (
            // Old word docs
            ('application/msword' == $mimeType && ('doc' == $uploadedFile->getExtension())) ||
            // New word documents
            (
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' == $mimeType &&
                ('docx' == $uploadedFile->getClientOriginalExtension() && ('' == $uploadedFile->getExtension()))
            )
        ) {
            return true;
        }

        return false;
    }
}
