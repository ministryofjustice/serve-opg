<?php

namespace App\Service\File\Checker;

use App\Service\File\Types\UploadableFileInterface;

class JpgChecker extends AbstractFileChecker implements FileCheckerInterface
{
    /**
     * Any other specific checks for a file type can go here
     */
    public function checkFile(UploadableFileInterface $file): UploadableFileInterface
    {
        return parent::checkFile($file);
    }
}
