<?php


namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function getIDsArray()
    {
        $alias = $this->getEntityAlias();
        $query = $this->createQueryBuilder($alias)->select($alias . '.id');
        return array_map(function ($result) { return $result['id']; }, $query->getQuery()->getScalarResult());
    }


    public function getEntityAlias()
    {
        $entityNameParts = explode('\\', $this->_entityName);
        return strtolower(end($entityNameParts));
    }
}