<?php


namespace App\Request;


use App\Entity\InventarioAlmacen;
use App\Entity\OrganizacionEmpresa;
use App\Entity\InventarioProductoMovimiento;

class MovimientoRetornoRequest
{
    /**
     * @var InventarioAlmacen
     */
    public $almacen_destino;

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
     * @var InventarioProductoMovimiento[]
     */
    public $producto_movimientos;

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
     * @param InventarioProductoMovimiento[] $producto_movimientos
     */
    public function addProductoMovimiento(InventarioProductoMovimiento $producto_movimiento): void
    {
        $this->producto_movimientos[] = $producto_movimiento;
    }
}