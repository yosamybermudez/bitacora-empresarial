<?php

namespace AppSistema\Entity;

use App\Entity\BaseEntity;
use AppSistema\Repository\SistemaModuloRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SistemaModuloRepository::class)
 */
class Modulo extends BaseEntity
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
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $identificador;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activado;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $icono_metroui;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $color_identificativo;

    public function __construct()
    {
        $this->activado = false;
        $this->color_identificativo = 'cyan';
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

    public function getIdentificador(): ?string
    {
        return $this->identificador;
    }

    public function setIdentificador(string $identificador): self
    {
        $this->identificador = $identificador;

        return $this;
    }

    public function isActivado(): ?bool
    {
        return $this->activado;
    }

    public function setActivado(bool $activado): self
    {
        $this->activado = $activado;

        return $this;
    }

    public function getIconoMetroui(): ?string
    {
        return $this->icono_metroui;
    }

    public function setIconoMetroui(string $icono_metroui): self
    {
        $this->icono_metroui = $icono_metroui;

        return $this;
    }

    public function getColorIdentificativo(): ?string
    {
        return $this->color_identificativo;
    }

    public function setColorIdentificativo(string $color_identificativo): self
    {
        $this->color_identificativo = $color_identificativo;

        return $this;
    }

    public function toJson(){
        $attributes = [
            'id',
            'nombre',
            'identificador'
        ];
        return $this->toJsonAttributes($attributes);
    }
}
