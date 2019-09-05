<?php


namespace App\Helpers\File;

use App\Exception\ServiceException;

abstract class FileCreatorAbstract implements FileCreatorInterface
{
    protected $dir;
    protected $fileName;
    protected $data;
    protected $ext;
    protected $path;
    /** @var FileEntity */
    protected $file;

    public function __construct(string $directory, string $fileName, string $data)
    {
        $this->setDir($directory);
        $this->setFileName($fileName);
        $this->setData($data);
        $this->file = new FileEntity($this->path, $this->ext);
    }

    public function setDir(string $directory)
    {
        if (!is_dir($directory)) {
            throw new ServiceException(sprintf('Directory "%s" doesn\'t exist', $directory), ServiceException::CODE_INVALID_CONFIG);
        }
        $this->dir = $directory;
    }

    public function setFileName(string $fileName)
    {
        $ext = FileHelper::getExt($fileName);
        if ($ext === null) {
            throw new ServiceException(sprintf('File name "%s" doesn\'t have extension', $fileName), ServiceException::CODE_INVALID_PARAMS);
        }
        $this->fileName = $fileName;
        $this->ext = $ext;
    }

    public function setData(string $data)
    {
        if ($this->dir === null) {
            throw new ServiceException('Files directory is required to create the file', ServiceException::CODE_INVALID_PARAMS);
        }
        if ($this->fileName === null) {
            throw new ServiceException('File name is required to create the file', ServiceException::CODE_INVALID_PARAMS);
        }
        if ($this->ext === null) {
            throw new ServiceException(sprintf('File name "%s" doesn\'t have extension', $this->fileName), ServiceException::CODE_INVALID_PARAMS);
        }
        $this->data = $data;
        $fastHash = FileHelper::getFastHash($this->data);
        if (strpos($this->fileName, $fastHash) !== 0) {
            $this->fileName = FileHelper::getFastHash($this->data) . '.' . $this->ext;
        }
        $this->path = str_replace(['\\', '\\\\', '/', '//', '\\/', '/\\'], DIRECTORY_SEPARATOR, $this->dir . DIRECTORY_SEPARATOR . $this->fileName);
    }

    public function getExt(): string
    {
        return $this->ext;
    }

    public function getName(): string
    {
        return $this->fileName;
    }

    public function getFile(): FileEntity
    {
        return $this->file;
    }
}