<?php

namespace ModInventario\Entity;

use App\Entity\BaseEntity;
use ModInventario\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AppSistema\Entity\Usuario;

/**
 * @ORM\Entity(repositoryClass=ProductoRepository::class)
 */
class Producto extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $unidadMedida;

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
     * @ORM\OneToMany(targetEntity=AlmacenProducto::class, mappedBy="producto")
     */
    private $almacenProductos;

    /**
     * @ORM\OneToMany(targetEntity=ProductoMovimiento::class, mappedBy="producto")
     */
    private $productoMovimientos;

    /**
     * @ORM\Column(type="float")
     */
    private $precioCompraCup;

    /**
     * @ORM\Column(type="float")
     */
    private $precioVentaCup;

    /**
     * @ORM\Column(type="float")
     */
    private $precioCompraMlc;

    /**
     * @ORM\Column(type="float")
     */
    private $precioVentaMlc;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $activo;

    public function __construct()
    {
        $this->activo = true;
        $this->precioCompraCup = 0;
        $this->precioVentaCup = 0;
        $this->precioCompraMlc = 0;
        $this->precioVentaMlc = 0;
        $this->almacenProductos = new ArrayCollection();
        $this->productoMovimientos = new ArrayCollection();
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

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

    public function getUnidadMedida(): ?string
    {
        return $this->unidadMedida;
    }

    public function setUnidadMedida(string $unidadMedida): self
    {
        $this->unidadMedida = $unidadMedida;

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

    /**
     * @return Collection<int, AlmacenProducto>
     */
    public function getAlmacenProductos(): Collection
    {
        return $this->almacenProductos;
    }

    public function addAlmacenProducto(AlmacenProducto $almacenProducto): self
    {
        if (!$this->almacenProductos->contains($almacenProducto)) {
            $this->almacenProductos[] = $almacenProducto;
            $almacenProducto->setProducto($this);
        }

        return $this;
    }

    public function removeAlmacenProducto(AlmacenProducto $almacenProducto): self
    {
        if ($this->almacenProductos->removeElement($almacenProducto)) {
            // set the owning side to null (unless already changed)
            if ($almacenProducto->getProducto() === $this) {
                $almacenProducto->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductoMovimiento>
     */
    public function getProductoMovimientos(): Collection
    {
        return $this->productoMovimientos;
    }

    public function addProductoMovimiento(ProductoMovimiento $productoMovimiento): self
    {
        if (!$this->productoMovimientos->contains($productoMovimiento)) {
            $this->productoMovimientos[] = $productoMovimiento;
            $productoMovimiento->setProducto($this);
        }

        return $this;
    }

    public function removeProductoMovimiento(ProductoMovimiento $productoMovimiento): self
    {
        if ($this->productoMovimientos->removeElement($productoMovimiento)) {
            // set the owning side to null (unless already changed)
            if ($productoMovimiento->getProducto() === $this) {
                $productoMovimiento->setProducto(null);
            }
        }

        return $this;
    }

    public function getPrecioCompraCup(): ?float
    {
        return $this->precioCompraCup;
    }

    public function setPrecioCompraCup(float $precioCompraCup): self
    {
        $this->precioCompraCup = $precioCompraCup;

        return $this;
    }

    public function getPrecioVentaCup(): ?float
    {
        return $this->precioVentaCup;
    }

    public function setPrecioVentaCup(float $precioVentaCup): self
    {
        $this->precioVentaCup = $precioVentaCup;

        return $this;
    }

    public function getPrecioCompraMlc(): ?float
    {
        return $this->precioCompraMlc;
    }

    public function setPrecioCompraMlc(float $precioCompraMlc): self
    {
        $this->precioCompraMlc = $precioCompraMlc;

        return $this;
    }

    public function getPrecioVentaMlc(): ?float
    {
        return $this->precioVentaMlc;
    }

    public function setPrecioVentaMlc(float $precioVentaMlc): self
    {
        $this->precioVentaMlc = $precioVentaMlc;

        return $this;
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(?bool $activo): self
    {
        $this->activo = $activo;

        return $this;
    }

    public function toJson(){
        $attributes = [
            'id',
            'nombre',
            'descripcion',
            'unidadMedida',
            'precioCompraCup',
            'precioVentaCup',
            'precioCompraMlc',
            'precioVentaMlc',
            'activo',
            'creadoEn',
            'actualizadoEn',
            'creadoPor',
            'actualizadoPor'
        ];
        return $this->toJsonAttributes($attributes);
    }
}
