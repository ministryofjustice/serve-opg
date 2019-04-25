<?php

namespace App\Service\File\Checker;

use App\Service\File\Types\UploadableFileInterface;

interface FileCheckerInterface
{
    /**
     * @param UploadableFileInterface $file
     *
     * @return mixed
     */
    public function checkFile(UploadableFileInterface $file);
}
