<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\ClamAVChecker;
use AppBundle\Service\File\Checker\DocChecker;
use Psr\Log\LoggerInterface;

class Doc extends UploadableFile
{
    protected $scannerEndpoint = 'upload/doc';

    public function __construct(
        ClamAVChecker $virusChecker,
        DocChecker $fileChecker,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }
}
