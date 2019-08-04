<?php

namespace App\Repository;

use App\Entity\Writer;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Writer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Writer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Writer[]    findAll()
 * @method Writer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WriterRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Writer::class);
    }

    public function findByName($name, $limit = null, $offset = null)
    {
        return $this->createQuery($limit, $offset)
            ->andWhere("LOWER(w.name) LIKE LOWER(CONCAT('%', :search_string, '%'))")
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
        $qb = $this->createQueryBuilder('w')->orderBy('w.name', 'ASC');
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }
        return $qb;
    }
}
