<?php

namespace App\Service\File\Checker;

use App\Service\File\Checker\Exception\InvalidFileTypeException;
use App\Service\File\Checker\Exception\RiskyFileException;
use App\Service\File\Types\Doc;
use App\Service\File\Types\Jpg;
use App\Service\File\Types\Pdf;
use App\Service\File\Types\Png;
use App\Service\File\Types\Tif;
use App\Service\File\Types\UploadableFile;
use App\Service\File\Types\UploadableFileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileCheckerFactory
{
    protected Pdf $pdf;

    protected Png $png;

    protected Jpg $jpg;

    protected Tif $tif;

    protected Doc $doc;

    protected LoggerInterface $logger;

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
     */
    public function factory(UploadedFile $uploadedFile): UploadableFile
    {
        if ($uploadedFile->isExecutable() ||
            preg_match('/([^\.])+(\.exe|\.bin|\.bat|\.js|\.zip|\.php)/i', $uploadedFile->getClientOriginalName())) {
            throw new InvalidFileTypeException( );
        }
        $mimeType = $uploadedFile->getMimeType();
        switch (true) {
            case ($mimeType == 'application/pdf'):
                return $this->pdf->setUploadedFile($uploadedFile);
            case ($mimeType == 'image/png'):
                return  $this->png->setUploadedFile($uploadedFile);
            case ($mimeType == 'image/jpeg'):
                return $this->jpg->setUploadedFile($uploadedFile);
            case ($mimeType == 'image/tiff'):
                return $this->tif->setUploadedFile($uploadedFile);
            case ($this->isWordDoc($uploadedFile)):
                return $this->doc->setUploadedFile($uploadedFile);
            default:
                throw new InvalidFileTypeException();
        }
    }

    /**
     * Is file a word docx file?
     */
    private function isWordDoc(UploadedFile $uploadedFile): bool
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
