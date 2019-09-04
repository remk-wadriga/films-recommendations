<?php


namespace App\Helpers\File;

abstract class AbstractFileReader implements FileReaderInterface
{
    /** @var FileEntity */
    protected $file;

    public function __construct(string $file, string $ext)
    {
        $this->file = new FileEntity($file, $ext);
    }

    public function getFile(): FileEntity
    {
        return $this->file;
    }

    public function writeData(array $data)
    {
        file_put_contents($this->file->path, $this->convertData($data));
    }

    abstract public function convertData(array $data): string;
}