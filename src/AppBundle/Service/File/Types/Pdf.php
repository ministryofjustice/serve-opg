<?php

namespace AppBundle\Service\File\Types;

class Pdf extends UploadableFile
{
    protected $scannerEndpoint = 'upload/pdf';
}
