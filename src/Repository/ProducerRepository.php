<?php

namespace App\Repository;

use App\Entity\Producer;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Producer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Producer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Producer[]    findAll()
 * @method Producer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProducerRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Producer::class);
    }

    public function findByName($name, $limit = null, $offset = null)
    {
        return $this->createQuery($limit, $offset)
            ->andWhere("LOWER(p.name) LIKE LOWER(CONCAT('%', :search_string, '%'))")
            ->setParameter('search_string', $name)
            ->getQuery()->getResult()
            ;
    }

    public function findForPage($limit = null, $offset = null)
    {
        return $this->createQuery($limit, $offset)->getQuery()->getResult();
    }

    public function createQuery($limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('p')->orderBy('p.name', 'ASC');
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }
        return $qb;
    }
}
