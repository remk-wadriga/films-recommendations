<?php


namespace App\TestService;

use App\Exception\ServiceException;
use App\Helpers\File\FileReaderFactory;
use App\Helpers\File\FileReaderInterface;
use App\Helpers\Web\WebReaderFactory;
use App\Helpers\Web\WebReaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractTestService
{
    protected $em;
    protected $container;
    protected $calc;

    /** @var FileReaderInterface[] */
    protected $fileReaders = [];

    /** @var WebReaderInterface[] */
    protected $webReaders = [];

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, Calculator $calc)
    {
        $this->em = $em;
        $this->container = $container;
        $this->calc = $calc;
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
        if (!file_exists($forFile)) {
            $forFile = $this->getParam('files_dir') . DIRECTORY_SEPARATOR . 'test_service' . DIRECTORY_SEPARATOR . $forFile;
        }
        return $this->fileReaders[$forFile] = FileReaderFactory::createFileReader($forFile);
    }

    public function getWebReader(string $type = null, array $config = []): WebReaderInterface
    {
        if ($type === null) {
            $type = WebReaderFactory::TYPE_HTML;
        }
        if (isset($this->webReaders[$type])) {
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