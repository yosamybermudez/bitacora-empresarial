<?php

namespace ModInventario\Repository;

use ModInventario\Entity\MovimientoSalidaDevolucion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovimientoSalidaDevolucion>
 *
 * @method MovimientoSalidaDevolucion|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovimientoSalidaDevolucion|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovimientoSalidaDevolucion[]    findAll()
 * @method MovimientoSalidaDevolucion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientoSalidaDevolucionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimientoSalidaDevolucion::class);
    }

    public function add(MovimientoSalidaDevolucion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MovimientoSalidaDevolucion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MovimientoSalidaDevolucion[] Returns an array of MovimientoSalidaDevolucion objects
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

//    public function findOneBySomeField($value): ?MovimientoSalidaDevolucion
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
