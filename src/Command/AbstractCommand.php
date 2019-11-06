<?php

namespace App\Command;

use App\Exception\ServiceException;
use App\Helpers\File\FileReaderFactory;
use App\Helpers\File\FileReaderInterface;
use App\Helpers\Web\WebReaderFactory;
use App\Helpers\Web\WebReaderInterface;
use App\TestService\AbstractEntity;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommand extends Command
{
    /** @var ObjectManager */
    protected $em;

    /** @var \Faker\Generator */
    protected $faker;

    /** @var ContainerInterface */
    protected $container;

    /** @var FileReaderInterface[] */
    protected $fileReaders = [];

    /** @var WebReaderInterface[] */
    protected $webReaders = [];

    protected $filesDir;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, $name = null)
    {
        parent::__construct($name);

        $this->em = $em;
        $this->container = $container;
        $this->filesDir = $this->getParam('files_dir');
    }

    protected function getParam($name, $defaultValue = null)
    {
        if (!$this->container->hasParameter($name)) {
            return $defaultValue;
        }
        return $this->container->getParameter($name);
    }

    public function getFileReader(string $forFile): FileReaderInterface
    {
        if (isset($this->fileReaders[$forFile])) {
            return $this->fileReaders[$forFile];
        }
        $forFile = $this->filesDir . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR . $forFile;
        return $this->fileReaders[$forFile] = FileReaderFactory::createFileReader($forFile);
    }

    public function getWebReader(string $type = null, array $config = [], bool $singleton = true): WebReaderInterface
    {
        if ($type === null) {
            $type = WebReaderFactory::TYPE_HTML;
        }
        if ($singleton && isset($this->webReaders[$type])) {
            return $this->webReaders[$type];
        }
        return $this->webReaders[$type] = WebReaderFactory::createWebReader($type, $config);
    }

    protected function createObject(string $entityClass, array $data): AbstractEntity
    {
        if (!class_exists($entityClass)) {
            throw new ServiceException(sprintf('Class "%s" does not exist', $entityClass), ServiceException::CODE_INVALID_CONFIG);
        }
        if (!is_subclass_of($entityClass, AbstractEntity::class)) {
            throw new ServiceException(sprintf('Class "%s" is not subclass of %s', $entityClass, AbstractEntity::class), ServiceException::CODE_INVALID_CONFIG);
        }

        return new $entityClass($data, $this);
    }
}