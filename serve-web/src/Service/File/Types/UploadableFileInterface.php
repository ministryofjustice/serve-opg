<?php

namespace App\Service\File\Types;

interface UploadableFileInterface
{
    public function checkFile(): void;

    public function callFileCheckers(): void;

    public function getFileCheckers(): array;
}
