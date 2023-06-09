<?php

namespace AppBase\Entity;

use App\Entity\BaseEntity;
use AppSistema\Entity\Usuario;
use Doctrine\ORM\Mapping as ORM;
use AppBase\Repository\BasePersonaRepository;

/**
 * @ORM\Entity(repositoryClass=BasePersonaRepository::class)
 */
class BasePersona extends BaseEntity
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
    private $nombres;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apellidos;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private $carneIdentidad;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $direccionParticular;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domicilio;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nivelEducacional;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombres(): ?string
    {
        return $this->nombres;
    }

    public function setNombres(string $nombres): self
    {
        $this->nombres = $nombres;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(?string $apellidos): self
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getCarneIdentidad(): ?string
    {
        return $this->carneIdentidad;
    }

    public function setCarneIdentidad(string $carneIdentidad): self
    {
        $this->carneIdentidad = $carneIdentidad;

        return $this;
    }

    public function getDireccionParticular(): ?string
    {
        return $this->direccionParticular;
    }

    public function setDireccionParticular(string $direccionParticular): self
    {
        $this->direccionParticular = $direccionParticular;

        return $this;
    }

    public function getDomicilio(): ?string
    {
        return $this->domicilio;
    }

    public function setDomicilio(?string $domicilio): self
    {
        $this->domicilio = $domicilio;

        return $this;
    }

    public function getNivelEducacional(): ?string
    {
        return $this->nivelEducacional;
    }

    public function setNivelEducacional(?string $nivelEducacional): self
    {
        $this->nivelEducacional = $nivelEducacional;

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
