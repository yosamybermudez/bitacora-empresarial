<?php

namespace AppSistema\Entity;

use App\Entity\BaseEntity;
use AppSistema\Repository\VariableConfiguracionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VariableConfiguracionRepository::class)
 */
class VariableConfiguracion extends BaseEntity
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
     * @ORM\Column(type="string", length=255)
     */
    private $valor;

    public function __construct(string $nombre, string $valor)
    {
        $this->nombre = $nombre;
        $this->valor = $valor;
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

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function toJson(){
        $attributes = [
            'id',
            'nombre',
            'valor'
        ];
        return $this->toJsonAttributes($attributes);
    }
}
