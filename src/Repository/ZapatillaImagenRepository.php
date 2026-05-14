<?php

namespace App\Repository;

use App\Entity\ZapatillaImagen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ZapatillaImagen>
 *
 * @method ZapatillaImagen|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapatillaImagen|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapatillaImagen[]    findAll()
 * @method ZapatillaImagen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapatillaImagenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ZapatillaImagen::class);
    }

    public function findByZapatillaOrdenadas($zapatillaId)
    {
        return $this->createQueryBuilder('zi')
            ->andWhere('zi.zapatilla = :zapatillaId')
            ->setParameter('zapatillaId', $zapatillaId)
            ->orderBy('zi.orden', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
