<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoSalidaGastoAporteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoSalidaGastoAporteRepository::class)
 */
class MovimientoSalidaGastoAporte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
