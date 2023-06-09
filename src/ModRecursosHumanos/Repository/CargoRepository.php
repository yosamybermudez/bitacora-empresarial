<?php

namespace ModRecursosHumanos\Repository;

use ModRecursosHumanos\Entity\Cargo;
use AppSistema\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CargoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cargo::class);
    }

    private function updateData(Cargo $entity, Usuario $usuario){
        try{
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
        } catch (\Exception $e){
            // LogFile Exception en AppController
        }
        return $entity;
    }

    public function add(Cargo $entity, Usuario $usuario, bool $flush = false): void
    {
        $entity = $this->updateData($entity, $usuario);
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cargo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
