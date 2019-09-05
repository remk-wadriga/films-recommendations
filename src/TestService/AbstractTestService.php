<?php


namespace App\TestService;

use App\Exception\ServiceException;
use App\Helpers\File\FileReaderFactory;
use App\Helpers\File\FileReaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractTestService
{
    protected $em;
    protected $container;

    /** @var FileReaderInterface[] */
    protected $fileReaders = [];

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getParam($name, $defaultValue = null)
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
        $forFile = $this->getParam('files_dir') . DIRECTORY_SEPARATOR . 'test_service' . DIRECTORY_SEPARATOR . $forFile;
        return $this->fileReaders[$forFile] = FileReaderFactory::createFileReader($forFile);
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