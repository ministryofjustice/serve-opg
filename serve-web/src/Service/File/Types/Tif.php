<?php

namespace App\Service\File\Types;

use App\Service\File\Checker\ClamAVChecker;
use App\Service\File\Checker\TifChecker;
use Psr\Log\LoggerInterface;

class Tif    extends UploadableFile
{
    protected $scannerEndpoint = 'upload/jpeg';

    public function __construct(
        ClamAVChecker $virusChecker,
        TifChecker $fileChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }
}
