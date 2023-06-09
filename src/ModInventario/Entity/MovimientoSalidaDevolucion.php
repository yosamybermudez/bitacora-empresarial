<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoSalidaDevolucionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoSalidaDevolucionRepository::class)
 */
class MovimientoSalidaDevolucion
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
