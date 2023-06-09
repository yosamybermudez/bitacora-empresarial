<?php

namespace ModInventario\Entity;

use App\Entity\BaseEntity;
use ModInventario\Repository\ProductoMovimientoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductoMovimientoRepository::class)
 */
class ProductoMovimiento extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class, inversedBy="productoMovimientos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $producto;


    /**
     * @ORM\Column(type="float")
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity=Movimiento::class, inversedBy="productoMovimientos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movimiento;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $precioCupVigente;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $precioMlcVigente;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducto(): ?Producto
    {
        return $this->producto;
    }

    public function setProducto(?Producto $producto): self
    {
        $this->producto = $producto;

        return $this;
    }

    public function getCantidad(): ?float
    {
        return $this->cantidad;
    }

    public function setCantidad(float $cantidad): self
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getMovimiento(): ?Movimiento
    {
        return $this->movimiento;
    }

    public function setMovimiento(?Movimiento $movimiento): self
    {
        $this->movimiento = $movimiento;

        return $this;
    }

    public function getPrecioCupVigente(): ?float
    {
        return $this->precioCupVigente;
    }

    public function setPrecioCupVigente(?float $precioCupVigente): self
    {
        $this->precioCupVigente = $precioCupVigente;

        return $this;
    }

    public function getPrecioMlcVigente(): ?float
    {
        return $this->precioMlcVigente;
    }

    public function setPrecioMlcVigente(?float $precioMlcVigente): self
    {
        $this->precioMlcVigente = $precioMlcVigente;

        return $this;
    }

    public function toJson(){
        $attributes = [
            'id'
        ];
        return $this->toJsonAttributes($attributes);
    }

}
