<?php

namespace ModInventario\Entity;

use ModInventario\Repository\MovimientoSalidaRegaloRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimientoSalidaRegaloRepository::class)
 */
class MovimientoSalidaRegalo
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
