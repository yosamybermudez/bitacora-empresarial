<?php


namespace App\Request;

use App\Entity\InventarioAlmacenProductoMovimiento;
use App\Entity\OrganizacionEmpresa;

class MovimientoVentaRapidaRequest
{
    /**
     * @var OrganizacionEmpresa
     */
    public $cliente;

    /**
     * @var string
     */
    public $codigo;

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