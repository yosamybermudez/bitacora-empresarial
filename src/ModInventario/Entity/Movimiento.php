<?php

namespace ModInventario\Entity;

use App\Entity\BaseEntity;
use AppBase\Entity\Empresa;
use ModInventario\Repository\MovimientoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AppSistema\Entity\Usuario;

/**
 * @ORM\Entity(repositoryClass=MovimientoRepository::class)
 */
class Movimiento extends BaseEntity
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
    private $estado;

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
     * @ORM\JoinColumn(nullable=false)
     */
    private $actualizadoPor;

    /**
     * @ORM\OneToOne(targetEntity=MovimientoEntradaCompra::class, mappedBy="movimiento", cascade={"persist", "remove"})
     */
    private $movimientoEntradaCompra;

    /**
     * @ORM\OneToOne(targetEntity=MovimientoSalidaVenta::class, mappedBy="movimiento", cascade={"persist", "remove"})
     */
    private $movimientoSalidaVenta;

    /**
     * @ORM\OneToOne(targetEntity=MovimientoSalidaDevolucion::class, mappedBy="movimiento", cascade={"persist", "remove"})
     */
    private $movimientoSalidaDevolucion;

    /**
     * @ORM\OneToOne(targetEntity=MovimientoEntradaDevolucion::class, mappedBy="movimiento", cascade={"persist", "remove"})
     */
    private $movimientoEntradaDevolucion;

    /**
     * @ORM\OneToOne(targetEntity=MovimientoInternoAjusteInventario::class, mappedBy="movimiento", cascade={"persist", "remove"})
     */
    private $movimientoInternoAjusteInventario;

    /**
     * @ORM\OneToOne(targetEntity=MovimientoInternoTransferenciaAlmacen::class, mappedBy="movimiento", cascade={"persist", "remove"})
     */
    private $movimientoInternoTransferenciaAlmacen;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="movimientos")
     */
    private $empresa;


    /**
     * @ORM\Column(type="string", length=15, nullable=true, unique=true)
     */
    private $codigo;

    /**
     * @ORM\OneToMany(targetEntity=MovimientoEstado::class, mappedBy="movimiento", orphanRemoval=true)
     */
    private $movimientoEstados;

    /**
     * @ORM\OneToMany(targetEntity=ProductoMovimiento::class, mappedBy="movimiento")
     */
    private $productoMovimientos;

    /**
     * @ORM\OneToMany(targetEntity=AlmacenProductoMovimiento::class, mappedBy="movimiento")
     */
    private $almacenProductoMovimientos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entregadoPorNombre;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $entregadoPorCI;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entregadoPorCargo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $entregadoPorFecha;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transportadoPorNombre;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $transportadoPorCI;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transportadoPorCargo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $transportadoPorFecha;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recibidoPorNombre;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $recibidoPorCI;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recibidoPorCargo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $recibidoPorFecha;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private $fecha;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $formaPago;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $lite;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private $importeTotalVigenteCup;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private $importeTotalVigenteMlc;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tipo;

    public function __construct()
    {
        $this->movimientoEstados = new ArrayCollection();
        $this->productoMovimientos = new ArrayCollection();
        $this->almacenProductoMovimientos = new ArrayCollection();
        $this->importeTotalVigenteCup = 0;
        $this->importeTotalVigenteMlc = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        $this->estado = $estado;

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

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

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

    /**
     * @return Collection<int, MovimientoEstado>
     */
    public function getMovimientoEstados(): Collection
    {
        return $this->movimientoEstados;
    }

    public function addMovimientoEstado(MovimientoEstado $movimientoEstado): self
    {
        if (!$this->movimientoEstados->contains($movimientoEstado)) {
            $this->movimientoEstados[] = $movimientoEstado;
            $movimientoEstado->setMovimiento($this);
        }

        return $this;
    }

    public function removeMovimientoEstado(MovimientoEstado $movimientoEstado): self
    {
        if ($this->movimientoEstados->removeElement($movimientoEstado)) {
            // set the owning side to null (unless already changed)
            if ($movimientoEstado->getMovimiento() === $this) {
                $movimientoEstado->setMovimiento(null);
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
            $productoMovimiento->setMovimiento($this);
        }

        return $this;
    }

    public function removeProductoMovimiento(ProductoMovimiento $productoMovimiento): self
    {
        if ($this->productoMovimientos->removeElement($productoMovimiento)) {
            // set the owning side to null (unless already changed)
            if ($productoMovimiento->getMovimiento() === $this) {
                $productoMovimiento->setMovimiento(null);
            }
        }

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
            $almacenProductoMovimiento->setMovimiento($this);
        }

        return $this;
    }

    public function removeAlmacenProductoMovimiento(AlmacenProductoMovimiento $almacenProductoMovimiento): self
    {
        if ($this->almacenProductoMovimientos->removeElement($almacenProductoMovimiento)) {
            // set the owning side to null (unless already changed)
            if ($almacenProductoMovimiento->getMovimiento() === $this) {
                $almacenProductoMovimiento->setMovimiento(null);
            }
        }

        return $this;
    }

    public function getEntregadoPorNombre(): ?string
    {
        return $this->entregadoPorNombre;
    }

    public function setEntregadoPorNombre(?string $entregadoPorNombre): self
    {
        $this->entregadoPorNombre = $entregadoPorNombre;

        return $this;
    }

    public function getEntregadoPorCI(): ?string
    {
        return $this->entregadoPorCI;
    }

    public function setEntregadoPorCI(?string $entregadoPorCI): self
    {
        $this->entregadoPorCI = $entregadoPorCI;

        return $this;
    }

    public function getEntregadoPorFecha(): ?\DateTimeInterface
    {
        return $this->entregadoPorFecha;
    }

    public function setEntregadoPorFecha(?\DateTimeInterface $entregadoPorFecha): self
    {
        $this->entregadoPorFecha = $entregadoPorFecha;

        return $this;
    }

    public function getEntregadoPorCargo(): ?string
    {
        return $this->entregadoPorCargo;
    }

    public function setEntregadoPorCargo(?string $entregadoPorCargo): self
    {
        $this->entregadoPorCargo = $entregadoPorCargo;

        return $this;
    }

    public function getTransportadoPorNombre(): ?string
    {
        return $this->transportadoPorNombre;
    }

    public function setTransportadoPorNombre(?string $transportadoPorNombre): self
    {
        $this->transportadoPorNombre = $transportadoPorNombre;

        return $this;
    }

    public function getTransportadoPorCI(): ?string
    {
        return $this->transportadoPorCI;
    }

    public function setTransportadoPorCI(?string $transportadoPorCI): self
    {
        $this->transportadoPorCI = $transportadoPorCI;

        return $this;
    }

    public function getTransportadoPorCargo(): ?string
    {
        return $this->transportadoPorCargo;
    }

    public function setTransportadoPorCargo(?string $transportadoPorCargo): self
    {
        $this->transportadoPorCargo = $transportadoPorCargo;

        return $this;
    }

    public function getTransportadoPorFecha(): ?\DateTimeInterface
    {
        return $this->transportadoPorFecha;
    }

    public function setTransportadoPorFecha(?\DateTimeInterface $transportadoPorFecha): self
    {
        $this->transportadoPorFecha = $transportadoPorFecha;

        return $this;
    }

    public function getRecibidoPorNombre(): ?string
    {
        return $this->recibidoPorNombre;
    }

    public function setRecibidoPorNombre(?string $recibidoPorNombre): self
    {
        $this->recibidoPorNombre = $recibidoPorNombre;

        return $this;
    }

    public function getRecibidoPorCI(): ?string
    {
        return $this->recibidoPorCI;
    }

    public function setRecibidoPorCI(?string $recibidoPorCI): self
    {
        $this->recibidoPorCI = $recibidoPorCI;

        return $this;
    }

    public function getRecibidoPorCargo(): ?string
    {
        return $this->recibidoPorCargo;
    }

    public function setRecibidoPorCargo(?string $recibidoPorCargo): self
    {
        $this->recibidoPorCargo = $recibidoPorCargo;

        return $this;
    }

    public function getRecibidoPorFecha(): ?\DateTimeInterface
    {
        return $this->recibidoPorFecha;
    }

    public function setRecibidoPorFecha(?\DateTimeInterface $recibidoPorFecha): self
    {
        $this->recibidoPorFecha = $recibidoPorFecha;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getFormaPago(): ?string
    {
        return $this->formaPago;
    }

    public function setFormaPago(?string $formaPago): self
    {
        $this->formaPago = $formaPago;

        return $this;
    }

    public function isLite(): ?bool
    {
        return $this->lite;
    }

    public function setLite(?bool $lite): self
    {
        $this->lite = $lite;

        return $this;
    }

    public function getImporteTotalVigenteCup(): ?float
    {
        return $this->importeTotalVigenteCup;
    }

    public function setImporteTotalVigenteCup(?float $importeTotalVigenteCup): self
    {
        $this->importeTotalVigenteCup = $importeTotalVigenteCup;

        return $this;
    }

    public function getImporteTotalVigenteMlc(): ?float
    {
        return $this->importeTotalVigenteMlc;
    }

    public function setImporteTotalVigenteMlc(?float $importeTotalVigenteMlc): self
    {
        $this->importeTotalVigenteMlc = $importeTotalVigenteMlc;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMovimientoEntradaCompra()
    {
        return $this->movimientoEntradaCompra;
    }

    /**
     * @param mixed $movimientoEntradaCompra
     */
    public function setMovimientoEntradaCompra($movimientoEntradaCompra): void
    {
        $this->movimientoEntradaCompra = $movimientoEntradaCompra;
    }

    /**
     * @return mixed
     */
    public function getMovimientoSalidaVenta()
    {
        return $this->movimientoSalidaVenta;
    }

    /**
     * @param mixed $movimientoSalidaVenta
     */
    public function setMovimientoSalidaVenta($movimientoSalidaVenta): void
    {
        $this->movimientoSalidaVenta = $movimientoSalidaVenta;
    }

    /**
     * @return mixed
     */
    public function getMovimientoSalidaDevolucion()
    {
        return $this->movimientoSalidaDevolucion;
    }

    /**
     * @param mixed $movimientoSalidaDevolucion
     */
    public function setMovimientoSalidaDevolucion($movimientoSalidaDevolucion): void
    {
        $this->movimientoSalidaDevolucion = $movimientoSalidaDevolucion;
    }

    /**
     * @return mixed
     */
    public function getMovimientoEntradaDevolucion()
    {
        return $this->movimientoEntradaDevolucion;
    }

    /**
     * @param mixed $movimientoEntradaDevolucion
     */
    public function setMovimientoEntradaDevolucion($movimientoEntradaDevolucion): void
    {
        $this->movimientoEntradaDevolucion = $movimientoEntradaDevolucion;
    }

    /**
     * @return mixed
     */
    public function getMovimientoInternoAjusteInventario()
    {
        return $this->movimientoInternoAjusteInventario;
    }

    /**
     * @param mixed $movimientoInternoAjusteInventario
     */
    public function setMovimientoInternoAjusteInventario($movimientoInternoAjusteInventario): void
    {
        $this->movimientoInternoAjusteInventario = $movimientoInternoAjusteInventario;
    }

    /**
     * @return mixed
     */
    public function getMovimientoInternoTransferenciaAlmacen()
    {
        return $this->movimientoInternoTransferenciaAlmacen;
    }

    /**
     * @param mixed $movimientoInternoTransferenciaAlmacen
     */
    public function setMovimientoInternoTransferenciaAlmacen($movimientoInternoTransferenciaAlmacen): void
    {
        $this->movimientoInternoTransferenciaAlmacen = $movimientoInternoTransferenciaAlmacen;
    }

    public function toJson(){
        $attributes = [
            'id',
            'creadoEn',
            'actualizadoEn',
            'creadoPor',
            'actualizadoPor'
        ];
        return $this->toJsonAttributes($attributes);
    }

}
