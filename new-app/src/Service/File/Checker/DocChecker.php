<?php

namespace App\Service\File\Checker;

use App\Service\File\Types\UploadableFileInterface;

class DocChecker extends AbstractFileChecker implements FileCheckerInterface
{
    /**
     * Any other specific checks for a file type can go here
     *
     * Checks file extension.
     *
     * @param  UploadableFileInterface $file
     * @return bool
     */
    public function checkFile(UploadableFileInterface $fileToStore)
    {
        return parent::checkFile($fileToStore);
    }
}
