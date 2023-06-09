<?php

namespace ModRecursosHumanos\Entity;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use ModRecursosHumanos\Repository\CargoRepository;
use AppSistema\Entity\Usuario;
use AppBase\Entity\BaseCargo;

/**
 * @ORM\Entity(repositoryClass=CargoRepository::class)
 */
class Cargo extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

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
     * @ORM\Column(type="float")
     */
    private $salarioEscala;

    /**
     * @ORM\OneToOne(targetEntity=Cargo::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $baseCargo;

    /**
     * @ORM\Column(type="integer")
     */
    private $escalaSalarial;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $formaPago;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSalarioEscala(): ?float
    {
        return $this->salarioEscala;
    }

    public function setSalarioEscala(float $salarioEscala): self
    {
        $this->salarioEscala = $salarioEscala;

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

    public function getCargo(): ?OrganizacionCargo
    {
        return $this->cargo;
    }

    public function setCargo(OrganizacionCargo $cargo): self
    {
        $this->cargo = $cargo;

        return $this;
    }

    public function getEscalaSalarial(): ?int
    {
        return $this->escalaSalarial;
    }

    public function setEscalaSalarial(int $escalaSalarial): self
    {
        $this->escalaSalarial = $escalaSalarial;

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

    /**
     * @return mixed
     */
    public function getBaseCargo()
    {
        return $this->baseCargo;
    }

    /**
     * @param mixed $baseCargo
     */
    public function setBaseCargo($baseCargo): void
    {
        $this->baseCargo = $baseCargo;
    }

}
