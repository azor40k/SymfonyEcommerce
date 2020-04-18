<?php

namespace App\Repository;

use App\Entity\PriceUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PriceUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceUnit[]    findAll()
 * @method PriceUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceUnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceUnit::class);
    }

    // /**
    //  * @return PriceUnit[] Returns an array of PriceUnit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PriceUnit
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
