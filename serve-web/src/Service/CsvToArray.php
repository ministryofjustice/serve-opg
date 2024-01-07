<?php

namespace App\Service;

use RuntimeException;

class CsvToArray
{
    const DELIMITER = ',';
    const ENCLOSURE = '"';
    const ESCAPE = '\\';

    /**
     * @var resource|false
     */
    private $handle;

    private array $expectedColumns = [];

    private bool $normaliseNewLines;

    private array|false $firstRow = [];

    private function getRow(): array|false
    {
        return fgetcsv($this->handle, 2000, self::DELIMITER, self::ENCLOSURE, self::ESCAPE);
    }

    /**
     * @throws RuntimeException
     */
    public function __construct(string $filePath, array $expectedColumns, bool $normaliseNewLines)
    {
        $this->expectedColumns = $expectedColumns;
        $this->normaliseNewLines = $normaliseNewLines;

        if (!file_exists($filePath)) {
            throw new RuntimeException("file $filePath not found");
        }

        // if line endings need to be normalised, the stream is replaced with a string stream with the content replaced
        if ($this->normaliseNewLines) {
            $content = str_replace(["\r\n", "\r"], ["\n", "\n"], file_get_contents($filePath));
            $this->handle = fopen('data://text/plain,' . $content, 'r');
        } else {
            ini_set('auto_detect_line_endings', true);
            $this->handle = fopen($filePath, 'r');
        }
    }

    public function getFirstRow(): array|false
    {
        if (empty($this->firstRow)) {
            $this->firstRow = $this->getRow();
        }

        return $this->firstRow;
    }

    public function getData(): array
    {
        $ret = [];

        // parse header
        $header = $this->getFirstRow();
        if (!$header) {
            throw new RuntimeException('Empty or corrupted file, cannot parse CSV header');
        }
        $missingColumns = array_diff($this->expectedColumns, $header);
        if ($missingColumns) {
            throw new RuntimeException('Invalid file. Cannot find expected header columns ' . implode(', ', $missingColumns));
        }

        // read rows
        $rowNumber = 1;
        while (($row = $this->getRow()) !== false) {
            $rowNumber++;
            $rowArray = [];
            foreach ($this->expectedColumns as $expectedColumn) {
                $index = array_search($expectedColumn, $header);
                if ($index !== false) {
                    if (!array_key_exists($index, $row)) {
                        throw new RuntimeException("Can't find $expectedColumn column in line $rowNumber");
                    }
                    $rowArray[$expectedColumn] = $row[$index];
                }
            }
            $ret[] = $rowArray;
        }

        return $ret;
    }

    public function __destruct()
    {
        fclose($this->handle);

        if (!$this->normaliseNewLines) {
            ini_set('auto_detect_line_endings', false);
        }
    }
}
