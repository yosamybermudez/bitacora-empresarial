<?php


namespace App\Request;

use App\Entity\InventarioAlmacenProductoMovimiento;
use App\Entity\OrganizacionEmpresa;

class MovimientoAjusteInventarioRequest
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
     * @var InventarioAlmacenProductoMovimiento[]
     */
    public $almacen_producto_movimientos;

    /**
     * @param InventarioAlmacenProductoMovimiento[] $almacen_producto_movimientos
     */
    public function addAlmacenProductoMovimiento(InventarioAlmacenProductoMovimiento $almacen_producto_movimiento): void
    {
        $this->almacen_producto_movimientos[] = $almacen_producto_movimiento;
    }
}