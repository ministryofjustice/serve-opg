<?php

namespace App\Service\File\Checker\Exception;

class VirusFoundException extends \RuntimeException
{
    protected $message = 'Found virus in file';
}
