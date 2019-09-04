<?php

namespace App\DataFixtures;

use App\Exception\ServiceException;
use App\Helpers\File\FileReaderFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFixture extends Fixture
{
    /** @var ObjectManager */
    private $em;

    /** @var \Faker\Generator */
    protected $faker;

    /** @var ContainerInterface */
    protected $container;

    protected $isEnabled = true;

    private $referencesIndex = [];

    private $filteredReferencesIndex = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    abstract protected function loadData(ObjectManager $em);

    public function load(ObjectManager $em)
    {
        if (!$this->isEnabled) {
            return;
        }

        $this->em = $em;
        $this->faker = Factory::create();
        $this->loadData($em);
    }

    protected function createMany(string $className, int $count, callable $factory, $index = 0)
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);
            $this->em->persist($entity);
            // store for usage later as App\Entity\ClassName_#COUNT#
            $this->addReference($className . '_' . $index . '_' . $i, $entity);
        }
    }

    protected function loadFromFile(string $className, string $file, $requiredAttributes = [], callable $factory = null, $index = 0)
    {
        $file = $this->container->getParameter('files_dir') . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($file)) {
            return;
        }

        if ($factory === null) {
            $factory = function ($entity, array $data, $i) use ($className) {
                if (!is_object($entity) || !($entity instanceof $className)) {
                    $entityClass = is_object($entity) ? get_class($entity) : json_encode($entity);
                    throw new ServiceException(sprintf('The dynamic entity must be instance of %s class, but it is "%s"', $className, $entityClass), ServiceException::CODE_INVALID_CONFIG);
                }
                foreach ($data as $attr => $value) {
                    $setter = 'set' . ucfirst($attr);
                    if (!method_exists($entity, $setter)) {
                        throw new ServiceException(sprintf('Entity %s has no "%s" method but it is required to set the data: %s',
                            $className, $setter, json_encode($data)), ServiceException::CODE_INVALID_CONFIG);
                    }
                    $entity->$setter($value);
                }
            };
        }

        $fileReader = FileReaderFactory::createFileReader($file);
        foreach ($fileReader->readFile($requiredAttributes) as $i => $data) {
            $entity = new $className();
            $factory($entity, $data, $i);
            $this->em->persist($entity);
            // store for usage later as App\Entity\ClassName_#COUNT#
            $this->addReference($className . '_' . $index . '_' . $i, $entity);
        }
    }

    protected function getRandomReference(string $className, $conditions = [])
    {
        if (!isset($this->filteredReferencesIndex[$className])) {
            $this->filteredReferencesIndex[$className] = [];
        }
        if (!isset($this->referencesIndex[$className])) {
            $this->referencesIndex[$className] = [];
            foreach ($this->referenceRepository->getReferences() as $key => $ref) {
                if (strpos($key, $className . '_') === 0) {
                    $this->referencesIndex[$className][] = $key;
                    if (!empty($conditions)) {
                        foreach ($conditions as $attr => $value) {
                            $getter = 'get' . ucfirst($attr);
                            if (method_exists($ref, $getter) && $ref->$getter() == $value) {
                                $this->filteredReferencesIndex[$className][] = $key;
                            }
                        }
                    }
                }
            }
        }

        if (empty($this->referencesIndex[$className])) {
            foreach ($this->em->getRepository($className)->findAll() as $i => $entity) {
                $index = $className . '_' . $i;
                $this->addReference($index, $entity);
                $this->referencesIndex[$className][] = $index;
            }
        }

        if (empty($this->referencesIndex[$className])) {
            throw new \Exception(sprintf('Cannot find any references for class "%s"', $className));
        }

        if (empty($conditions)) {
            $randomReferenceKey = $this->faker->randomElement($this->referencesIndex[$className]);
        } elseif (!empty($this->filteredReferencesIndex[$className])) {
            $randomReferenceKey = $this->faker->randomElement($this->filteredReferencesIndex[$className]);
        } else {
            return null;
        }

        return $this->getReference($randomReferenceKey);
    }

    protected function getRandomReferences(string $className, int $count = 0)
    {
        $references = [];
        if ($count === 0) {
            $this->getRandomReference($className);
            if (empty($this->referencesIndex[$className])) {
                throw new \Exception(sprintf('Cannot find any references for class "%s"', $className));
            }
            foreach ($this->referencesIndex[$className] as $index) {
                $references[] = $this->getReference($index);
            }
        } else {
            while (count($references) < $count) {
                $references[] = $this->getRandomReference($className);
            }
        }
        return $references;
    }
}
