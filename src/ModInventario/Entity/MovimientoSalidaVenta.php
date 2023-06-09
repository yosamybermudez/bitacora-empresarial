<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoSalidaVentaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoSalidaVentaRepository::class)
 */
class MovimientoSalidaVenta
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
