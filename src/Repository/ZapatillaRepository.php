<?php

namespace App\Repository;

use App\Entity\Zapatilla;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Zapatilla>
 */
class ZapatillaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zapatilla::class);
    }

    //    /**
    //     * @return Zapatilla[] Returns an array of Zapatilla objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('z.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Zapatilla
    //    {
    //        return $this->createQueryBuilder('z')
    //            ->andWhere('z.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
