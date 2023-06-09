<?php

namespace ModInventario\Repository;

use ModInventario\Entity\MovimientoInternoTransferenciaAlmacen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovimientoInternoTransferenciaAlmacen>
 *
 * @method MovimientoInternoTransferenciaAlmacen|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovimientoInternoTransferenciaAlmacen|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovimientoInternoTransferenciaAlmacen[]    findAll()
 * @method MovimientoInternoTransferenciaAlmacen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientoInternoTransferenciaAlmacenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimientoInternoTransferenciaAlmacen::class);
    }

    public function add(MovimientoInternoTransferenciaAlmacen $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MovimientoInternoTransferenciaAlmacen $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MovimientoInternoTransferenciaAlmacen[] Returns an array of MovimientoInternoTransferenciaAlmacen objects
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

//    public function findOneBySomeField($value): ?MovimientoInternoTransferenciaAlmacen
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
