<?php

namespace ModInventario\Repository;

use ModInventario\Entity\MovimientoSalidaVenta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovimientoSalidaVenta>
 *
 * @method MovimientoSalidaVenta|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovimientoSalidaVenta|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovimientoSalidaVenta[]    findAll()
 * @method MovimientoSalidaVenta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientoSalidaVentaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimientoSalidaVenta::class);
    }

    public function add(MovimientoSalidaVenta $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MovimientoSalidaVenta $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MovimientoSalidaVenta[] Returns an array of MovimientoSalidaVenta objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MovimientoSalidaVenta
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
