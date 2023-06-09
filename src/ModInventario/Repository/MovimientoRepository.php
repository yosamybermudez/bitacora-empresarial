<?php

namespace ModInventario\Repository;

use ModInventario\Entity\Movimiento;
use ModInventario\Entity\MovimientoAjusteInventario;
use ModInventario\Entity\MovimientoDevolucion;
use ModInventario\Entity\MovimientoEntrada;
use ModInventario\Entity\MovimientoGastoAporte;
use ModInventario\Entity\MovimientoRetorno;
use ModInventario\Entity\MovimientoTransferenciaAlmacen;
use ModInventario\Entity\MovimientoVenta;
use ModInventario\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movimiento>
 *
 * @method Movimiento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movimiento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movimiento[]    findAll()
 * @method Movimiento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovimientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movimiento::class);
    }

    public function add(Movimiento $entity, Usuario $usuario, bool $flush = false): void
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

    public function remove(Movimiento $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Movimiento[] Returns an array of Movimiento objects
     */
    public function findMovimientosPorTipo(string $frecuencia = 'anno'): array
    {
        $fecha = new \DateTime();
        $query = $this->createQueryBuilder('m');
        $query
            ->select('m.tipo', 'count(m) as cantidad');
        switch ($frecuencia){
            case "dia": {
                $query
                    ->where('m.fecha = :fecha')
                    ->setParameter('fecha', $fecha->format('Y-m-d'));
                break;
            }
            case "mes": {
                $primer_dia = clone $fecha;
                $ultimo_dia = clone $fecha;
                $primer_dia->modify('first day of this month');
                $ultimo_dia->modify('last day of this month');
                $query
                    ->where('m.fecha between :inicio and :fin')
                    ->setParameter('inicio', $primer_dia)
                    ->setParameter('fin', $ultimo_dia);
                break;
            }
            case "anno": {
                $anno = $fecha->format('Y');
                $query
                    ->where('m.fecha between :inicio and :fin')
                    ->setParameter('inicio', $anno . '-01-01')
                    ->setParameter('fin', $anno . '-12-31');
                break;
            }
        }

        return $query
            ->andWhere('m.estado = :estado')
            ->setParameter('estado', 'Confirmado')
            ->groupBy('m.tipo')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Movimiento[] Returns an array of Movimiento objects
     */
    public function findMovimientosFecha(string $tipo, ?\DateTime $fecha ): array
    {
        $query = $this->createQueryBuilder('m');

        switch ($tipo){
            case "": {
                break;
            }
            case "": {
                break;
            }
            case "": {
                break;
            }
            case "": {
                break;
            }
            case "": {
                break;
            }
            case "": {
                break;
            }
            case "": {
                break;
            }




            case "compras": {
                $query
                    ->innerJoin(MovimientoEntrada::class, 'me')
                    ->where('m.id = me.movimiento');
                break;
            }
            case "ventas": {
                $query
                    ->innerJoin(MovimientoVenta::class, 'mv')
                    ->where('m.id = mv.movimiento');
                break;
            }
            case "retornos": {
                $query
                    ->innerJoin(MovimientoRetorno::class, 'mr')
                    ->where('m.id = mr.movimiento');
                break;
            }
            case "devoluciones": {
                $query
                    ->innerJoin(MovimientoDevolucion::class, 'md')
                    ->where('m.id = md.movimiento');
                break;
            }
            case "ajustesInventario": {
                $query
                    ->innerJoin(MovimientoAjusteInventario::class, 'mai')
                    ->where('m.id = mai.movimiento');

                break;
            }
            case "transferenciasAlmacen": {
                $query
                    ->innerJoin(MovimientoTransferenciaAlmacen::class, 'mta')
                    ->where('m.id = mta.movimiento');
                break;
            }
            case "gastosAporte": {
                $query
                    ->innerJoin(MovimientoGastoAporte::class, 'mga')
                    ->where('m.id = mga.movimiento');
                break;
            }
            default: {

                break;
            }
        }
        if($fecha){
            $query
                ->andWhere('m.fecha = :fecha')
                ->setParameter('fecha', $fecha->format('Y-m-d'));
        }

        return $query
            ->orderBy('m.codigo', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Movimiento[] Returns an array of Movimiento objects
     */
    public function findResumenFecha(\DateTime $fecha = null, string $frecuencia = 'dia', string $tipo = ''): array
    {
        $query = $this->createQueryBuilder('m');

        if ($fecha) {
            switch ($frecuencia){
                case "dia": {
                    $query
                        ->where('m.fecha = :fecha')
                        ->setParameter('fecha', $fecha->format('Y-m-d'));
                    break;
                }
                case "mes": {
                    $primer_dia = clone $fecha;
                    $ultimo_dia = clone $fecha;
                    $primer_dia->modify('first day of this month');
                    $ultimo_dia->modify('last day of this month');
                    $query
                        ->where('m.fecha between :inicio and :fin')
                        ->setParameter('inicio', $primer_dia)
                        ->setParameter('fin', $ultimo_dia);
                    break;
                }
                case "anno": {
                    $anno = $fecha->format('Y');
                    $query
                        ->where('m.fecha between :inicio and :fin')
                        ->setParameter('inicio', $anno . '-01-01')
                        ->setParameter('fin', $anno . '-12-31');
                    break;
                }

            }
        }

        switch ($tipo){
            case "entradas": {
                $query->select('p.id', 'p.nombre', 'pm.cantidad', 'p.precioCompraCup', 'p.precioCompraMlc', 'p.precioVentaCup', 'p.precioVentaMlc');
                $query
                    ->andWhere('pm.cantidad > 0')
                    ->innerJoin('m.movimientoEntrada', 'mr')
                    ->innerJoin('m.productoMovimientos', 'pm')
                    ->leftJoin('pm.producto', 'p')
                ;
                $query->distinct();
                break;
            }
            case "ventas": {
                $query->select('p.id', 'p.nombre', 'apm.cantidad', 'p.precioCompraCup', 'p.precioCompraMlc', 'p.precioVentaCup', 'p.precioVentaMlc');
                $query
                    ->andWhere('apm.cantidad > 0')
                    ->innerJoin('m.movimientoVenta', 'mv')
                    ->innerJoin('m.almacenProductoMovimientos', 'apm')
                    ->innerJoin('apm.almacenProducto', 'ap')
                    ->innerJoin('ap.producto', 'p');
                break;
            }
            case "retornos": {
                $query->select('p.id', 'p.nombre', 'pm.cantidad', 'p.precioCompraCup', 'p.precioCompraMlc', 'p.precioVentaCup', 'p.precioVentaMlc');
                $query
                    ->andWhere('pm.cantidad > 0')
                    ->innerJoin('m.movimientoRetorno', 'mr')
                    ->innerJoin('m.productoMovimientos', 'pm')
                    ->innerJoin('pm.producto', 'p');
                break;
            }
            case "devoluciones": {
                $query->select('p.id', 'p.nombre', 'apm.cantidad', 'p.precioCompraCup', 'p.precioCompraMlc', 'p.precioVentaCup', 'p.precioVentaMlc');
                $query
                    ->andWhere('apm.cantidad > 0')
                    ->innerJoin('m.movimientoDevolucion', 'md')
                    ->innerJoin('m.almacenProductoMovimientos', 'apm')
                    ->innerJoin('apm.almacenProducto', 'ap')
                    ->innerJoin('ap.producto', 'p');
                break;
            }
            case "gastos de aporte":
            case "gastosDeAporte": {
                $query->select('p.id', 'p.nombre', 'apm.cantidad', 'p.precioCompraCup', 'p.precioCompraMlc', 'p.precioVentaCup', 'p.precioVentaMlc');
                $query
                    ->andWhere('apm.cantidad > 0')
                    ->innerJoin('m.movimientoDevolucion', 'mg')
                    ->innerJoin('m.almacenProductoMovimientos', 'apm')
                    ->innerJoin('apm.almacenProducto', 'ap')
                    ->innerJoin('ap.producto', 'p');
                break;
            }
            case "transferencias": {
                $query->select('p.id', 'p.nombre', 'apm.cantidad', 'p.precioCompraCup', 'p.precioCompraMlc', 'p.precioVentaCup', 'p.precioVentaMlc');
                $query
                    ->andWhere('apm.cantidad > 0')
                    ->innerJoin('m.movimientoTransferenciaAlmacen', 'mta')
                    ->innerJoin('m.almacenProductoMovimientos', 'apm')
                    ->innerJoin('apm.almacenProducto', 'ap')
                    ->innerJoin('ap.producto', 'p');
                break;
            }
            case "ajustes": {
                $query->select('p.id', 'p.nombre', 'apm.cantidad', 'p.precioCompraCup', 'p.precioCompraMlc', 'p.precioVentaCup', 'p.precioVentaMlc');
                $query
                    ->andWhere('apm.cantidad > 0')
                    ->innerJoin('m.movimientoAjusteInventario', 'mai')
                    ->innerJoin('m.almacenProductoMovimientos', 'apm')
                    ->innerJoin('apm.almacenProducto', 'ap')
                    ->innerJoin('ap.producto', 'p');
                break;
            }
            default: {
                dd($tipo);
                break;
            }
        }
        $query
            ->andWhere('m.estado = :estado')
            ->setParameter('estado', 'Confirmado')
            ->addSelect('m.fecha', 'p.unidadMedida', 'm.codigo')
            ->orderBy('m.codigo', 'DESC');

        return $query
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Movimiento[] Returns an array of Movimiento objects
     */
    public function findMovimiento(string $seleccion, string $tipo, int $id): ?Movimiento
    {
        $query = $this->createQueryBuilder('m');

        if($seleccion === 'anterior'){
            $query
                ->where('m.id < :id')
                ->setParameter('id', $id)
                ->orderBy('m.id', 'DESC');
        } elseif($seleccion === 'siguiente'){
            $query
                ->where('m.id > :id')
                ->setParameter('id', $id)
                ->orderBy('m.id', 'ASC');
        }


        switch ($tipo){
            case "ENT": {
                $query
                    ->innerJoin(MovimientoEntrada::class, 'me');
                break;
            }
            case "VEN": {
                $query
                    ->innerJoin(MovimientoVenta::class, 'mv');
                break;
            }
            case "RET": {
                $query
                    ->innerJoin(MovimientoRetorno::class, 'mr');
                break;
            }
            case "DEV": {
                $query
                    ->innerJoin(MovimientoDevolucion::class, 'md');
                break;
            }
            default: {

                break;
            }
        }
        return $query
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


/// //    /**
//     * @return Movimiento[] Returns an array of Movimiento objects
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

//    public function findOneBySomeField($value): ?Movimiento
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
