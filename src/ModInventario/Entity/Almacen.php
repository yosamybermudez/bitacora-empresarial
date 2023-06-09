<?php

namespace ModInventario\Entity;

use App\Entity\BaseEntity;
use ModInventario\Repository\AlmacenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AppSistema\Entity\Usuario;

/**
 * @ORM\Entity(repositoryClass=AlmacenRepository::class)
 */
class Almacen extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

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
     * @ORM\JoinColumn(nullable=false, referencedColumnName="id")
     */
    private $actualizadoPor;

    /**
     * @ORM\OneToMany(targetEntity=AlmacenProducto::class, mappedBy="almacen")
     */
    private $almacenProductos;

    /**
     * @ORM\OneToMany(targetEntity=Movimiento::class, mappedBy="almacen")
     */
    private $movimientos;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $codigo;

    public function __construct()
    {
        $this->almacenProductos = new ArrayCollection();
        $this->movimientos = new ArrayCollection();
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
            $almacenProducto->setAlmacen($this);
        }

        return $this;
    }

    public function removeAlmacenProducto(AlmacenProducto $almacenProducto): self
    {
        if ($this->almacenProductos->removeElement($almacenProducto)) {
            // set the owning side to null (unless already changed)
            if ($almacenProducto->getAlmacen() === $this) {
                $almacenProducto->setAlmacen(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Movimiento>
     */
    public function getMovimientos(): Collection
    {
        return $this->movimientos;
    }

    public function addMovimiento(Movimiento $movimiento): self
    {
        if (!$this->movimientos->contains($movimiento)) {
            $this->movimientos[] = $movimiento;
            $movimiento->setAlmacen($this);
        }

        return $this;
    }

    public function removeMovimiento(Movimiento $movimiento): self
    {
        if ($this->movimientos->removeElement($movimiento)) {
            // set the owning side to null (unless already changed)
            if ($movimiento->getAlmacen() === $this) {
                $movimiento->setAlmacen(null);
            }
        }

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function toJson(){
        $attributes = [
            'id',
            'nombre',
            'descripcion',
            'codigo',
            'creadoEn',
            'actualizadoEn',
            'creadoPor',
            'actualizadoPor'
        ];
        return $this->toJsonAttributes($attributes);
    }
}
