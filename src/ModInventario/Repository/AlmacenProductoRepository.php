<?php

namespace ModInventario\Repository;

use ModInventario\Entity\AlmacenProducto;
use ModInventario\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AlmacenProducto>
 *
 * @method AlmacenProducto|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlmacenProducto|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlmacenProducto[]    findAll()
 * @method AlmacenProducto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlmacenProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlmacenProducto::class);
    }

    public function add(AlmacenProducto $entity, Usuario $usuario, bool $flush = false): void
    {
        if (is_callable(array($entity, 'getCreadoEn')) and is_callable(array($entity, 'setCreadoEn'))) {
            if ($entity->getCreadoEn() === null){
                $entity->setCreadoEn(new \DateTime());
            }
        }
        if (is_callable(array($entity, 'getCreadoPor')) and is_callable(array($entity, 'setCreadoPor'))) {
            if ($entity->getCreadoPor() === null){
                $entity->setCreadoPor($usuario);
            }
        }
        if (is_callable(array($entity, 'setActualizadoEn'))) {
            $entity->setActualizadoEn(new \DateTime());
        }
        if (is_callable(array($entity, 'setActualizadoPor'))) {
            $entity->setActualizadoPor($usuario);
        }

        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AlmacenProducto $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findExistenciasDisponibles(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.saldoDisponible > 0')
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }



//    /**
//     * @return AlmacenProducto[] Returns an array of AlmacenProducto objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AlmacenProducto
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
