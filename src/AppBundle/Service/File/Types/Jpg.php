<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\ClamAVChecker;
use AppBundle\Service\File\Checker\JpgChecker;
use Psr\Log\LoggerInterface;

class Jpg extends UploadableFile
{
    protected $scannerEndpoint = 'upload/jpeg';

    public function __construct(
        ClamAVChecker $virusChecker,
        JpgChecker $fileChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }
}
