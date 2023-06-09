<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoInternoTransferenciaAlmacenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoInternoTransferenciaAlmacenRepository::class)
 */
class MovimientoInternoTransferenciaAlmacen
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
