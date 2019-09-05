<?php


namespace App\Helpers\File;


interface FileCreatorInterface
{
    public function setDir(string $directory);

    public function setFileName(string $fileName);

    public function setData(string $data);

    public function getExt(): string;

    public function getName(): string;

    public function create(string $size = null): FileEntity;

    public function getFile(): FileEntity;
}