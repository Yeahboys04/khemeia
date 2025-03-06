<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shelvingunit
 */
#[ORM\Table(name: 'shelvingunit')]
#[ORM\UniqueConstraint(name: 'id_shelvingUnit', columns: ['id_shelvingUnit'])]
#[ORM\Index(columns: ['id_cupboard'], name: 'FK_shelvingUnit_cupboard')]
#[ORM\Entity]
class Shelvingunit
{
    #[ORM\Column(name: 'id_shelvingUnit', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idShelvingunit = null;

    #[ORM\Column(name: 'name_shelvingUnit', type: 'string', length: 100, nullable: false)]
    private ?string $nameShelvingunit = null;

    #[ORM\ManyToOne(targetEntity: Cupboard::class)]
    #[ORM\JoinColumn(name: 'id_cupboard', referencedColumnName: 'id_cupboard')]
    private ?Cupboard $idCupboard = null;

    public function getIdShelvingunit(): ?string
    {
        return $this->idShelvingunit;
    }

    public function getNameShelvingunit(): ?string
    {
        return $this->nameShelvingunit;
    }

    public function setNameShelvingunit(string $nameShelvingunit): self
    {
        $this->nameShelvingunit = $nameShelvingunit;

        return $this;
    }

    public function getIdCupboard(): ?Cupboard
    {
        return $this->idCupboard;
    }

    public function setIdCupboard(?Cupboard $idCupboard): self
    {
        $this->idCupboard = $idCupboard;

        return $this;
    }

    public function __toString(): string
    {
        //Nom du site - Nom de l'entrepôt - Nom de l'armoire : Nom de l'étagère
        $nameCupboard = $this->idCupboard->getNameCupboard();
        $stock = $this->idCupboard->getIdStock();
        $nameStock = $stock->getNameStock();
        $nameSite = $stock->getIdSite()->getNameSite();

        return $nameSite .
            " - " . $nameStock  .
            " - " . $nameCupboard  .
            " : " . $this->nameShelvingunit;
    }

    public function getHiddenName(): string
    {
        //Nom du site
        $stock = $this->idCupboard->getIdStock();
        $nameSite = $stock->getIdSite()->getNameSite();

        return $nameSite;
    }

    public function getLocalName(): string
    {
        //Nom de l'entrepôt - Nom de l'armoire : Nom de l'étagère
        $nameCupboard = $this->idCupboard->getNameCupboard();
        $stock = $this->idCupboard->getIdStock();
        $nameStock = $stock->getNameStock();

        return $nameStock  .
            " - " . $nameCupboard  .
            " : " . $this->nameShelvingunit;
    }
}