<?php

namespace App\Repository;

use App\Entity\Company;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function findByName($name, $limit = null, $offset = null)
    {
        return $this->createQuery($limit, $offset)
            ->andWhere("LOWER(c.name) LIKE LOWER(CONCAT('%', :search_string, '%'))")
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
        $qb = $this->createQueryBuilder('c')->orderBy('c.name', 'ASC');
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }
        return $qb;
    }
}
