<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Types\Jpg;
use AppBundle\Service\File\Types\Pdf;
use AppBundle\Service\File\Types\Png;
use AppBundle\Service\File\Types\UploadableFile;
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
     * FileCheckerFactory constructor.
     * @param Pdf $pdf
     * @param Png $png
     * @param Jpg $jpg
     */
    public function __construct(Pdf $pdf, Png $png, Jpg $jpg)
    {
        $this->pdf = $pdf;
        $this->png = $png;
        $this->jpg = $jpg;
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return UploadableFile
     */
    public function factory(UploadedFile $uploadedFile)
    {
        switch ($uploadedFile->getMimeType()) {
            case 'application/pdf':
                return $this->pdf->setUploadedFile($uploadedFile);
            case 'image/png':
                return  $this->png->setUploadedFile($uploadedFile);
            case 'image/jpeg':
                return $this->jpg->setUploadedFile($uploadedFile);
            default:
                throw new \RuntimeException('File type not supported');

        }
    }
}
