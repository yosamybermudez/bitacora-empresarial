<?php


namespace App\Request;

use App\Entity\InventarioAlmacen;
use App\Entity\InventarioAlmacenProductoMovimiento;
use App\Entity\OrganizacionEmpresa;

class MovimientoTransferenciaAlmacenRequest
{

    /**
     * @var \DateTime
     */
    public $fecha;

    /**
     * @var InventarioAlmacen
     */
    public $almacen_destino;

    /**
     * @var InventarioAlmacenProductoMovimiento[]
     */
    public $almacen_producto_movimientos;

    /**
     * @var string
     */
    public $descripcion;


    /**
     * @param InventarioAlmacenProductoMovimiento[] $almacen_producto_movimientos
     */
    public function addAlmacenProductoMovimiento(InventarioAlmacenProductoMovimiento $almacen_producto_movimiento): void
    {
        $this->almacen_producto_movimientos[] = $almacen_producto_movimiento;
    }
}