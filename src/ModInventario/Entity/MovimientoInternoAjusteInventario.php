<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoInternoAjusteInventarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoInternoAjusteInventarioRepository::class)
 */
class MovimientoInternoAjusteInventario
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
