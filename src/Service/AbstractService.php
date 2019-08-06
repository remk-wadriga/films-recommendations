<?php


namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractService
{
    protected $em;
    protected $container;

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
}