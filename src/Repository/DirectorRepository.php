<?php

namespace App\Repository;

use App\Entity\Director;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Director|null find($id, $lockMode = null, $lockVersion = null)
 * @method Director|null findOneBy(array $criteria, array $orderBy = null)
 * @method Director[]    findAll()
 * @method Director[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirectorRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Director::class);
    }

    public function findByName($name, $limit = null, $offset = null)
    {
        return $this->createQuery($limit, $offset)
            ->andWhere("LOWER(d.name) LIKE LOWER(CONCAT('%', :search_string, '%'))")
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
        $qb = $this->createQueryBuilder('d')->orderBy('d.name', 'ASC');
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }
        return $qb;
    }
}
