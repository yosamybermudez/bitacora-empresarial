<?php

namespace ModRecursosHumanos\Entity;

use App\Entity\BaseEntity;
use AppBase\Entity\BaseCargo;
use Doctrine\ORM\Mapping as ORM;
use ModRecursosHumanos\Repository\ContratoTrabajoRepository;
use AppSistema\Entity\Usuario;

/**
 * @ORM\Entity(repositoryClass=ContratoTrabajoRepository::class)
 */
class ContratoTrabajo extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Trabajador::class, inversedBy="recursosHumanosContratoTrabajos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trabajador;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numeroContrato;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaInicio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vigencia;

    /**
     * @ORM\ManyToOne(targetEntity=BaseCargo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $cargo;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrabajador(): ?Trabajador
    {
        return $this->trabajador;
    }

    public function setTrabajador(?Trabajador $trabajador): self
    {
        $this->trabajador = $trabajador;

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

    public function getNumeroContrato(): ?string
    {
        return $this->numeroContrato;
    }

    public function setNumeroContrato(string $numeroContrato): self
    {
        $this->numeroContrato = $numeroContrato;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getVigencia(): ?string
    {
        return $this->vigencia;
    }

    public function setVigencia(?string $vigencia): self
    {
        $this->vigencia = $vigencia;

        return $this;
    }

    public function getCargo(): ?BaseCargo
    {
        return $this->cargo;
    }

    public function setCargo(?BaseCargo $cargo): self
    {
        $this->cargo = $cargo;

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
}
