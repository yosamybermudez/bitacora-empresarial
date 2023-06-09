<?php

namespace AppSistema\Repository;

use AppSistema\Entity\VariableConfiguracion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VariableConfiguracion>
 *
 * @method VariableConfiguracion|null find($id, $lockMode = null, $lockVersion = null)
 * @method VariableConfiguracion|null findOneBy(array $criteria, array $orderBy = null)
 * @method VariableConfiguracion[]    findAll()
 * @method VariableConfiguracion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VariableConfiguracionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VariableConfiguracion::class);
    }

    public function add(VariableConfiguracion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VariableConfiguracion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return VariableConfiguracion[] Returns an array of VariableConfiguracion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VariableConfiguracion
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
