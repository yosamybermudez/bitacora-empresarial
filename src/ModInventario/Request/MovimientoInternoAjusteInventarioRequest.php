<?php


namespace ModInventario\Request;

use ModInventario\Entity\AlmacenProductoMovimiento;
use ModInventario\Entity\Organizacion;

class MovimientoInternoAjusteInventarioRequest
{

    /**
     * @var \DateTime
     */
    public $fecha;

    /**
     * @var string
     */
    public $descripcion;

    /**
     * @var string
     */
    public $motivo;

    /**
     * @var AlmacenProductoMovimiento[]
     */
    public $almacen_producto_movimientos;

    /**
     * @param AlmacenProductoMovimiento[] $almacen_producto_movimientos
     */
    public function addAlmacenProductoMovimiento(AlmacenProductoMovimiento $almacen_producto_movimiento): void
    {
        $this->almacen_producto_movimientos[] = $almacen_producto_movimiento;
    }
}