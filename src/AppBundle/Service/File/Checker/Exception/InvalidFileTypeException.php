<?php

namespace AppBundle\Service\File\Checker\Exception;

class InvalidFileTypeException extends \RuntimeException
{
    protected $message = 'File type not supported';
}
