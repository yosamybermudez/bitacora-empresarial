<?php

namespace ModInventario\Repository;

use ModInventario\Entity\MovimientoEntradaDevolucion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovimientoEntradaDevolucion>
 *
 * @method MovimientoEntradaDevolucion|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovimientoEntradaDevolucion|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovimientoEntradaDevolucion[]    findAll()
 * @method MovimientoEntradaDevolucion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientoEntradaDevolucionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimientoEntradaDevolucion::class);
    }

    public function add(MovimientoEntradaDevolucion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MovimientoEntradaDevolucion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MovimientoEntradaDevolucion[] Returns an array of MovimientoEntradaDevolucion objects
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

//    public function findOneBySomeField($value): ?MovimientoEntradaDevolucion
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
