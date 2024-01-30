<?php

namespace App\Service\File\Checker;

use App\Service\File\Checker\Exception\RiskyFileException;
use App\Service\File\Types\UploadableFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AbstractFileChecker
 * A generic place to hold common methods for checking files
 *
 * @package App\Service\File\Checker
 */
class AbstractFileChecker
{
    /**
     * Any other checks that are not found by the virus scan go here.
     * Checks file extension.
     */
    public function checkFile(UploadableFileInterface $file): UploadableFileInterface
    {
        if (!self::hasValidFileExtension($file)) {
            $extension = strtolower($file->getUploadedFile()->getClientOriginalExtension());

            throw new RiskyFileException('Invalid file extension: ' . $extension);
        }

        return $file;
    }

    /**
     * Has the file got a valid extension
     */
    protected static function hasValidFileExtension(UploadableFileInterface $file): bool
    {
        $extension = strtolower($file->getUploadedFile()->getClientOriginalExtension());

        if (!in_array($extension, self::getAcceptedExtensions())) {
            return false;
        }

        return true;
    }

    /**
     * List of accepted file extensions
     *
     * @todo generate list from config / env variables
     */
    public static function getAcceptedExtensions(): array
    {
        return ['pdf', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'doc', 'docx'];
    }
}
