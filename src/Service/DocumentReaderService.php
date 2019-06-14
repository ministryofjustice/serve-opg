<?php

namespace App\Service;

use LukeMadhanga\DocumentParser;

class DocumentReaderService
{
    /**
     * Get text from word document
     */
    public function readWordDocx($fileLocation)
    {
        return DocumentParser::parseFromFile($fileLocation);
    }
}
