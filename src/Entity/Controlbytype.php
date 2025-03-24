<?php

namespace App\Entity;

use App\Repository\ControlbytypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Controlbytype - Relation de compatibilité entre types de produits
 */
#[ORM\Table(name: 'controlbytype')]
#[ORM\Entity(repositoryClass: ControlbytypeRepository::class)]
class Controlbytype
{
    #[ORM\Id]
    #[ORM\Column(name: 'id_type1', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    private ?int $idType1 = null;

    #[ORM\Id]
    #[ORM\Column(name: 'id_type2', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    private ?int $idType2 = null;

    #[ORM\Column(name: 'isCompatible', type: 'boolean', nullable: false, options: ['default' => false])]
    private ?bool $iscompatible = null;

    public function getIdType1(): ?string
    {
        return $this->idType1;
    }

    public function setIdType1(string $idType1): self
    {
        $this->idType1 = $idType1;

        return $this;
    }

    public function getIdType2(): ?string
    {
        return $this->idType2;
    }

    public function setIdType2(string $idType2): self
    {
        $this->idType2 = $idType2;

        return $this;
    }

    public function getIscompatible(): ?bool
    {
        return $this->iscompatible;
    }

    public function setIscompatible(bool $iscompatible): self
    {
        $this->iscompatible = $iscompatible;

        return $this;
    }
}