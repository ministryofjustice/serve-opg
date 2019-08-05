<?php declare(strict_types=1);


namespace App\TestHelpers;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTestHelper extends WebTestCase
{
    /**
     * @param string $fileLocation
     * @param string $originalName
     * @param string $mimeType
     * @return UploadedFile
     */
    static public function createUploadedFile(string $fileLocation, string $originalName, string $mimeType)
    {
        self::bootKernel();
        $container = self::$container;
        $projectDir = $container->get('kernel')->getProjectDir();
        $location = $projectDir . $fileLocation;

        return new UploadedFile($location, $originalName, $mimeType, null);
    }
}
