<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cupboard
 */
#[ORM\Table(name: 'cupboard')]
#[ORM\UniqueConstraint(name: 'id_cupboard', columns: ['id_cupboard'])]
#[ORM\Index(name: 'FK_cupboard_stock', columns: ['id_stock'])]
#[ORM\Entity]
class Cupboard
{
    #[ORM\Column(name: 'id_cupboard', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idCupboard = null;

    #[ORM\Column(name: 'name_cupboard', type: 'string', length: 100, nullable: false)]
    private ?string $nameCupboard = null;

    #[ORM\ManyToOne(targetEntity: Stock::class)]
    #[ORM\JoinColumn(name: 'id_stock', referencedColumnName: 'id_stock')]
    private ?Stock $idStock = null;

    public function getIdCupboard(): ?string
    {
        return $this->idCupboard;
    }

    public function getNameCupboard(): ?string
    {
        return $this->nameCupboard;
    }

    public function setNameCupboard(string $nameCupboard): self
    {
        $this->nameCupboard = $nameCupboard;

        return $this;
    }

    public function getIdStock(): ?Stock
    {
        return $this->idStock;
    }

    public function setIdStock(?Stock $idStock): self
    {
        $this->idStock = $idStock;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameCupboard;
    }
}