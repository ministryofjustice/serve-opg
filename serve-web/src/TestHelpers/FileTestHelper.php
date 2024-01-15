<?php declare(strict_types=1);


namespace App\TestHelpers;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTestHelper extends WebTestCase
{
    static public function createUploadedFile(string $fileLocation, string $originalName, string $mimeType): UploadedFile
    {
        self::bootKernel();
        $projectDir = self::bootKernel()->getProjectDir();
        $location = $projectDir . $fileLocation;

        return new UploadedFile($location, $originalName, $mimeType, null);
    }

    static public function countCsvRows(string $fileLocation, bool $excludeHeaderRow): int
    {
        $csvRows = file($fileLocation);
        return $excludeHeaderRow ? count($csvRows) - 1 : count($csvRows);
    }
}
