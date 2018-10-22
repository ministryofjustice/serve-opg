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
        switch ($uploadedFile->getMimeType()) {
            case 'application/pdf':
                return $this->pdf->setUploadedFile($uploadedFile);
            case 'application/msword':
                return $this->doc->setUploadedFile($uploadedFile);
            case 'image/png':
                return  $this->png->setUploadedFile($uploadedFile);
            case 'image/jpeg':
                return $this->jpg->setUploadedFile($uploadedFile);
            case 'image/tiff':
                return $this->tif->setUploadedFile($uploadedFile);
            default:
                throw new InvalidFileTypeException();
        }
    }
}
