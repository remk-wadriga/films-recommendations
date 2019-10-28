<?php


namespace App\Helpers\File;

use App\Exception\FileException;


class CacheFileReader extends AbstractFileReader
{
    public function readFile($requiredAttributes = []): array
    {
        if ($this->file->data !== null) {
            return $this->file->data;
        }

        if (!is_readable($this->file->path)) {
            throw new FileException(sprintf('File %s is not readable', $this->file->path), FileException::NOT_READABLE);
        }

        $data = unserialize(file_get_contents($this->file->path));
        return $this->file->data = $data !== null ? $data : [];
    }

    public function convertData(array $data): string
    {
        return serialize($data);
    }

}