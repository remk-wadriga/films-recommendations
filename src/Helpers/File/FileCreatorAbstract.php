<?php


namespace App\Helpers\File;

use App\Exception\ServiceException;

abstract class FileCreatorAbstract implements FileCreatorInterface
{
    protected $dir;
    protected $name;
    protected $data;
    protected $ext;
    protected $path;
    /** @var FileEntity */
    protected $file;

    public function __construct(string $directory, string $name, string $data)
    {
        $this->setDir($directory);
        $this->setName($name);
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

    public function setName(string $name)
    {
        $ext = FileHelper::getExt($name);
        if ($ext === null) {
            throw new ServiceException(sprintf('File name "%s" doesn\'t have extension', $name), ServiceException::CODE_INVALID_PARAMS);
        }
        $this->name = $name;
        $this->ext = $ext;
    }

    public function setData(string $data)
    {
        if ($this->dir === null) {
            throw new ServiceException('Files directory is required to create the file', ServiceException::CODE_INVALID_PARAMS);
        }
        if ($this->name === null) {
            throw new ServiceException('File name is required to create the file', ServiceException::CODE_INVALID_PARAMS);
        }
        if ($this->ext === null) {
            throw new ServiceException(sprintf('File name "%s" doesn\'t have extension', $this->name), ServiceException::CODE_INVALID_PARAMS);
        }
        $this->data = $data;
        $fastHash = FileHelper::getFastHash($this->data);
        if (strpos($this->name, $fastHash) !== 0) {
            $this->name = FileHelper::getFastHash($this->data) . '.' . $this->ext;
        }
        $this->path = str_replace(['\\', '\\\\', '/', '//', '\\/', '/\\'], DIRECTORY_SEPARATOR, $this->dir . DIRECTORY_SEPARATOR . $this->name);
    }

    public function getExt(): string
    {
        return $this->ext;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFile(): FileEntity
    {
        return $this->file;
    }
}