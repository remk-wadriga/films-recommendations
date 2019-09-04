<?php


namespace App\Helpers\File;


interface FileReaderInterface
{
    public function readFile($requiredAttributes = []): array;

    public function writeData(array $data);

    public function getFile(): FileEntity;
}