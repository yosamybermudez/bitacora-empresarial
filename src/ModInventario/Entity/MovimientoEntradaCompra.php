<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoEntradaCompraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoEntradaCompraRepository::class)
 */
class MovimientoEntradaCompra
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
