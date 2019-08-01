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

    // /**
    //  * @return Writer[] Returns an array of Writer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Writer
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
