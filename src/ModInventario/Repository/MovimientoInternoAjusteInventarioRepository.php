<?php

namespace ModInventario\Repository;

use ModInventario\Entity\MovimientoInternoAjusteInventario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovimientoInternoAjusteInventario>
 *
 * @method MovimientoInternoAjusteInventario|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovimientoInternoAjusteInventario|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovimientoInternoAjusteInventario[]    findAll()
 * @method MovimientoInternoAjusteInventario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientoInternoAjusteInventarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimientoInternoAjusteInventario::class);
    }

    public function add(MovimientoInternoAjusteInventario $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MovimientoInternoAjusteInventario $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MovimientoInternoAjusteInventario[] Returns an array of MovimientoInternoAjusteInventario objects
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

//    public function findOneBySomeField($value): ?MovimientoInternoAjusteInventario
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
