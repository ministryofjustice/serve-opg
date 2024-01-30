<?php

namespace App\Service\File\Checker;

use App\Service\File\Types\UploadableFileInterface;

interface FileCheckerInterface
{
    public function checkFile(UploadableFileInterface $file): UploadableFileInterface;
}
