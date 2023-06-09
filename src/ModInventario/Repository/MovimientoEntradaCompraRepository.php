<?php

namespace ModInventario\Repository;

use ModInventario\Entity\MovimientoEntradaCompra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovimientoEntradaCompra>
 *
 * @method MovimientoEntradaCompra|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovimientoEntradaCompra|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovimientoEntradaCompra[]    findAll()
 * @method MovimientoEntradaCompra[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientoEntradaCompraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimientoEntradaCompra::class);
    }

    public function add(MovimientoEntradaCompra $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MovimientoEntradaCompra $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MovimientoEntradaCompra[] Returns an array of MovimientoEntradaCompra objects
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

//    public function findOneBySomeField($value): ?MovimientoEntradaCompra
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
