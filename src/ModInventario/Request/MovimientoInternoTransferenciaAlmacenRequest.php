<?php


namespace ModInventario\Request;

use ModInventario\Entity\Almacen;
use ModInventario\Entity\AlmacenProductoMovimiento;
use ModInventario\Entity\Organizacion;

class MovimientoInternoTransferenciaAlmacenRequest
{

    /**
     * @var \DateTime
     */
    public $fecha;

    /**
     * @var Almacen
     */
    public $almacen_destino;

    /**
     * @var AlmacenProductoMovimiento[]
     */
    public $almacen_producto_movimientos;

    /**
     * @var string
     */
    public $descripcion;


    /**
     * @param AlmacenProductoMovimiento[] $almacen_producto_movimientos
     */
    public function addAlmacenProductoMovimiento(AlmacenProductoMovimiento $almacen_producto_movimiento): void
    {
        $this->almacen_producto_movimientos[] = $almacen_producto_movimiento;
    }
}