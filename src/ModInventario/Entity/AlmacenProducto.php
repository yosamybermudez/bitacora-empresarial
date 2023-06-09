<?php

namespace ModInventario\Entity;

use App\Entity\BaseEntity;
use ModInventario\Repository\AlmacenProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AppSistema\Entity\Usuario;

/**
 * @ORM\Entity(repositoryClass=AlmacenProductoRepository::class)
 */
class AlmacenProducto extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Producto::class, inversedBy="almacenProductos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $producto;

    /**
     * @ORM\ManyToOne(targetEntity=Almacen::class, inversedBy="almacenProductos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $almacen;

    /**
     * @ORM\Column(type="float")
     */
    private $saldoContable;

    /**
     * @ORM\Column(type="float")
     */
    private $saldoDisponible;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creadoEn;

    /**
     * @ORM\Column(type="datetime")
     */
    private $actualizadoEn;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $creadoPor;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $actualizadoPor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity=AlmacenProductoMovimiento::class, mappedBy="almacenProducto")
     */
    private $almacenProductoMovimientos;

    public function __construct()
    {
        $this->almacenProductoMovimientos = new ArrayCollection();
    }

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

    public function getAlmacen(): ?Almacen
    {
        return $this->almacen;
    }

    public function setAlmacen(?Almacen $almacen): self
    {
        $this->almacen = $almacen;

        return $this;
    }

    public function getSaldoContable(): ?float
    {
        return $this->saldoContable;
    }

    public function setSaldoContable(float $saldoContable): self
    {
        $this->saldoContable = $saldoContable;

        return $this;
    }

    public function getSaldoDisponible(): ?float
    {
        return $this->saldoDisponible;
    }

    public function setSaldoDisponible(float $saldoDisponible): self
    {
        $this->saldoDisponible = $saldoDisponible;

        return $this;
    }

    public function getCreadoEn(): ?\DateTimeInterface
    {
        return $this->creadoEn;
    }

    public function setCreadoEn(\DateTimeInterface $creadoEn): self
    {
        $this->creadoEn = $creadoEn;

        return $this;
    }

    public function getActualizadoEn(): ?\DateTimeInterface
    {
        return $this->actualizadoEn;
    }

    public function setActualizadoEn(\DateTimeInterface $actualizadoEn): self
    {
        $this->actualizadoEn = $actualizadoEn;

        return $this;
    }

    public function getCreadoPor(): ?Usuario
    {
        return $this->creadoPor;
    }

    public function setCreadoPor(?Usuario $creadoPor): self
    {
        $this->creadoPor = $creadoPor;

        return $this;
    }

    public function getActualizadoPor(): ?Usuario
    {
        return $this->actualizadoPor;
    }

    public function setActualizadoPor(?Usuario $actualizadoPor): self
    {
        $this->actualizadoPor = $actualizadoPor;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * @return Collection<int, AlmacenProductoMovimiento>
     */
    public function getAlmacenProductoMovimientos(): Collection
    {
        return $this->almacenProductoMovimientos;
    }

    public function addAlmacenProductoMovimiento(AlmacenProductoMovimiento $almacenProductoMovimiento): self
    {
        if (!$this->almacenProductoMovimientos->contains($almacenProductoMovimiento)) {
            $this->almacenProductoMovimientos[] = $almacenProductoMovimiento;
            $almacenProductoMovimiento->setAlmacenProducto($this);
        }

        return $this;
    }

    public function removeAlmacenProductoMovimiento(AlmacenProductoMovimiento $almacenProductoMovimiento): self
    {
        if ($this->almacenProductoMovimientos->removeElement($almacenProductoMovimiento)) {
            // set the owning side to null (unless already changed)
            if ($almacenProductoMovimiento->getAlmacenProducto() === $this) {
                $almacenProductoMovimiento->setAlmacenProducto(null);
            }
        }

        return $this;
    }

    public function getNombreCompleto(){
        return sprintf('%s / %s',
            $this->getProducto()->getNombre(),
            $this->getAlmacen()->getNombre()
        );
    }

    public function getNombreCompletoPrecio(){
        return sprintf('%s / %s - %s CUP - %s MLC - Existencias: %s %s',
            $this->getAlmacen()->getNombre(),
            $this->getProducto()->getNombre(),
            number_format($this->getProducto()->getPrecioVentaCup(),2,'.', ''),
            number_format($this->getProducto()->getPrecioVentaMlc(),2,'.', ''),
            $this->getSaldoDisponible(),
            $this->getProducto()->getUnidadMedida()
        );
    }

    public function toJson(){
        $attributes = [
            'id',
            'producto',
            'almacen',
            'descripcion',
            'saldoContable',
            'saldoDisponible',
            'creadoEn',
            'actualizadoEn',
            'creadoPor',
            'actualizadoPor'
        ];
        return $this->toJsonAttributes($attributes);
    }
}
