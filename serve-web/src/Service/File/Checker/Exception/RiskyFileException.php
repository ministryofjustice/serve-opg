<?php

namespace App\Service\File\Checker\Exception;

class RiskyFileException extends \RuntimeException
{
    protected $message = 'Invalid PDF';
}
