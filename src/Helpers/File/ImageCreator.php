<?php

namespace App\Helpers\File;

use App\Exception\FileException;

class ImageCreator extends FileCreatorAbstract
{
    private $defaultSize = '600x400';

    public function create(string $size = null): FileEntity
    {
        if ($size === null) {
            $size = $this->defaultSize;
        }
        if (!preg_match("/^(\d+)x(\d+)$/", $size, $matches) || !is_array($matches) || count($matches) !== 3) {
            throw new FileException(sprintf('Invalid size given: "%s", it must have a format like this: "<width>x<height>"', $size), FileException::CODE_INVALID_PARAMS);
        }
        list($x, $y) = [$matches[1], $matches[2]];

        $this->data = base64_decode($this->data);
        if (empty($this->data) && !file_exists($this->file->path)) {
            throw new FileException('Invalid file data. It must be a valid base-64 encoded string', FileException::INVALID_FORMAT);
        }

        if (!file_exists($this->file->path)) {
            file_put_contents($this->file->path, $this->data);
        }

        FileHelper::cropImage($this->file, $x, $y);

        return $this->file;
    }
}