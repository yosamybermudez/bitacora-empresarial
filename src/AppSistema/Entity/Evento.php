<?php

namespace AppSistema\Entity;

use App\Entity\BaseEntity;
use AppSistema\Repository\EventoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventoRepository::class)
 */
class Evento extends BaseEntity
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
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $origen;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nivel;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registradoEn;

    public function __construct($nivel, $origen, $registradoPor, $descripcion, $tipo, $informacionExtra = '')
    {
        $this->descripcion = $descripcion;
        $this->nivel = $nivel;
        $this->origen = $origen;
        $this->registradoPor = $registradoPor;
        $this->registradoEn = new \DateTime();
        $this->tipo = $tipo;
        $this->informacionExtra = $informacionExtra;
    }

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="eventos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $registradoPor;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $informacionExtra;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getOrigen(): ?string
    {
        return $this->origen;
    }

    public function setOrigen(string $origen): self
    {
        $this->origen = $origen;

        return $this;
    }

    public function getNivel(): ?string
    {
        return $this->nivel;
    }

    public function setNivel(string $nivel): self
    {
        $this->nivel = $nivel;

        return $this;
    }

    public function getRegistradoEn(): ?\DateTimeInterface
    {
        return $this->registradoEn;
    }

    public function setRegistradoEn(\DateTimeInterface $registradoEn): self
    {
        $this->registradoEn = $registradoEn;

        return $this;
    }

    public function getRegistradoPor(): ?Usuario
    {
        return $this->registradoPor;
    }

    public function setRegistradoPor(?Usuario $registradoPor): self
    {
        $this->registradoPor = $registradoPor;

        return $this;
    }

    public function getTipo(): ?string
    {
        switch ($this->tipo){
            case 0: return 'No especificado'; break;
            case 1: return 'Registar entidad'; break;
            case 2: return 'Modificar entidad'; break;
            case 3: return 'Eliminar entidad'; break;
            case 4: return 'Excepción del sistema'; break;
            case 5: return 'Inicio de sesión'; break;
            case 6: return 'Cierre de sesión'; break;
            case 7: return 'Error del sistema'; break;
            default: return 'No especificado'; break;
        }
    }

    public function setTipo(?int $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function toJson(){
        $attributes = [
            'id',
            'registradoEn',
            'registradoPor'
        ];
        return $this->toJsonAttributes($attributes);
    }

    public function getInformacionExtra(): ?string
    {
        return $this->informacionExtra;
    }

    public function setInformacionExtra(?string $informacionExtra): self
    {
        $this->informacionExtra = $informacionExtra;

        return $this;
    }
}
