<?php

namespace App\Service;

use LukeMadhanga\DocumentParser;

class DocumentReaderService
{
    /**
     * Get text from word document - supports .doc and .docx
     *
     * @param string $fileLocation, the path of the word doc to parse
     * @return \LukeMadhanga\html|string
     */
    public function readWordDoc(string $fileLocation)
    {
        return DocumentParser::parseFromFile($fileLocation);
    }
}
