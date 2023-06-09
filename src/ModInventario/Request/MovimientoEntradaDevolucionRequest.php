<?php


namespace ModInventario\Request;


use ModInventario\Entity\Almacen;
use ModInventario\Entity\Organizacion;
use ModInventario\Entity\ProductoMovimiento;

class MovimientoEntradaDevolucionRequest
{
    /**
     * @var Almacen
     */
    public $almacen_destino;

    /**
     * @var Organizacion
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
     * @var ProductoMovimiento[]
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
     * @param ProductoMovimiento[] $producto_movimientos
     */
    public function addProductoMovimiento(ProductoMovimiento $producto_movimiento): void
    {
        $this->producto_movimientos[] = $producto_movimiento;
    }
}