<?php

namespace App\Service;

use LukeMadhanga\DocumentParser;

class DocumentReaderService
{
    /**
     * Get text from word document
     */
    public function readWordDocx($doc)
    {
        return DocumentParser::parseFromFile($doc);
    }
}
