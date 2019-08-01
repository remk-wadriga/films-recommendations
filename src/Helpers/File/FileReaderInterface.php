<?php


namespace App\Helpers\File;


interface FileReaderInterface
{
    public function readFile($requiredAttributes = []): array;

    public function getFile(): FileEntity;
}