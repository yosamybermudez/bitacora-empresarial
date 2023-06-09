<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoEntradaDevolucionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoEntradaDevolucionRepository::class)
 */
class MovimientoEntradaDevolucion
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
