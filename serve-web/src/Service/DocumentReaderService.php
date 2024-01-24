<?php

namespace App\Service;

use LukeMadhanga\DocumentParser;

class DocumentReaderService
{
    /**
     * Get text from word document - supports .doc and .docx
     */
    public function readWordDoc(string $fileLocation): string
    {
        return DocumentParser::parseFromFile($fileLocation);
    }
}
