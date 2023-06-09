<?php

namespace ModRecursosHumanos\Entity;

use App\Entity\BaseEntity;
use AppBase\Entity\BaseCargo;
use AppBase\Entity\BasePersona;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ModRecursosHumanos\Repository\TrabajadorRepository;
use AppSistema\Entity\Usuario;

/**
 * @ORM\Entity(repositoryClass=TrabajadorRepository::class)
 */
class Trabajador extends BaseEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=BasePersona::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $persona;

    /**
     * @ORM\ManyToOne(targetEntity=BaseCargo::class, inversedBy="trabajadores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cargo;

    /**
     * @ORM\OneToMany(targetEntity=ContratoTrabajo::class, mappedBy="trabajador")
     */
    private $contratosTrabajo;

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

    public function __construct()
    {
        $this->contratosTrabajo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersona(): ?BasePersona
    {
        return $this->persona;
    }

    public function setPersona(BasePersona $persona): self
    {
        $this->persona = $persona;

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

    /**
     * @return mixed
     */
    public function getContratosTrabajo()
    {
        return $this->contratosTrabajo;
    }

    /**
     * @param mixed $contratosTrabajo
     */
    public function setContratosTrabajo($contratosTrabajo): void
    {
        $this->contratosTrabajo = $contratosTrabajo;
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
