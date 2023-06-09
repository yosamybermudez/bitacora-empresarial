<?php


namespace App\Request;

use App\Entity\InventarioAlmacenProductoMovimiento;
use App\Entity\OrganizacionEmpresa;

class MovimientoVentaRequest
{
    /**
     * @var OrganizacionEmpresa
     */
    public $cliente;

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
    public $codigo;

    /**
     * @var string
     */
    public $formaPago;

    /**
     * @var InventarioAlmacenProductoMovimiento[]
     */
    public $almacen_producto_movimientos;

    /**
     * @var string
     */
    public $entregado_por_nombre;

    /**
     * @var string
     */
    public $entregado_por_cargo;

    /**
     * @var string
     */
    public $entregado_por_ci;

    /**
     * @var \DateTime
     */
    public $entregado_por_fecha;

    /**
     * @var string
     */
    public $transportado_por_nombre;

    /**
     * @var string
     */
    public $transportado_por_cargo;

    /**
     * @var string
     */
    public $transportado_por_ci;

    /**
     * @var \DateTime
     */
    public $transportado_por_fecha;

    /**
     * @var string
     */
    public $recibido_por_nombre;

    /**
     * @var string
     */
    public $recibido_por_cargo;

    /**
     * @var string
     */
    public $recibido_por_ci;

    /**
     * @var \DateTime
     */
    public $recibido_por_fecha;

    /**
     * @param InventarioAlmacenProductoMovimiento[] $almacen_producto_movimientos
     */
    public function addAlmacenProductoMovimiento(InventarioAlmacenProductoMovimiento $almacen_producto_movimiento): void
    {
        $this->almacen_producto_movimientos[] = $almacen_producto_movimiento;
    }
}