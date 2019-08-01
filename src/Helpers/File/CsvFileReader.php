<?php


namespace App\Helpers\File;

use App\Exception\FileException;

class CsvFileReader extends AbstractFileReader
{
    protected $sep = ';';

    public function readFile($requiredAttributes = []): array
    {
        if ($this->file->data !== null) {
            return $this->file->data;
        }

        if (!is_readable($this->file->path)) {
            throw new FileException(sprintf('File %s is not readable', $this->file->path), FileException::NOT_READABLE);
        }

        $this->file->data = [];
        $lineNumber = 0;
        $fields = [];
        $fieldsCount = 0;
        $handle = fopen($this->file->path, 'r');

        while (($line = fgets($handle)) !== false) {
            $lineParts = explode($this->sep, trim($line));
            $lineNumber++;
            if (empty($lineParts)) {
                continue;
            }
            if (empty($fields)) {
                foreach ($requiredAttributes as $attr) {
                    if (!in_array($attr, $lineParts)) {
                        throw new FileException(sprintf('Invalid file %s format: the first string (attributes) must contains all of those attributes: %s',
                            $this->file->path, implode(', ', $requiredAttributes)), FileException::INVALID_FORMAT);
                    }
                }
                $fields = $lineParts;
                $fieldsCount = count($fields);
                continue;
            }

            $linePartsCount = count($lineParts);
            if ($linePartsCount !== $fieldsCount) {
                throw new FileException(sprintf('Invalid file %s format: each string must have a %s parts separated by "%s". String %s has a %s parts',
                    $this->file->path, $fieldsCount, $this->sep, $lineNumber, $linePartsCount), FileException::INVALID_FORMAT);
            }

            $tmpData = [];
            foreach ($lineParts as $index => $value) {
                $tmpData[$fields[$index]] = trim($value);
            }
            $this->file->data[] = $tmpData;
        }

        fclose($handle);

        return $this->file->data;
    }
}