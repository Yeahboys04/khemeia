<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Controlbytype
 */
#[ORM\Table(name: 'controlbytype')]
#[ORM\UniqueConstraint(name: 'id_controlbytype', columns: ['id_controlbytype'])]
#[ORM\Entity]
class Controlbytype
{
    #[ORM\Column(name: 'id_controlbytype', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idControlbytype = null;

    #[ORM\Column(name: 'id_type1', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    private ?int $idType1 = null;

    #[ORM\Column(name: 'id_type2', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    private ?int $idType2 = null;

    #[ORM\Column(name: 'isCompatible', type: 'boolean', nullable: false)]
    private ?bool $iscompatible = null;

    public function getIdControlbytype(): ?string
    {
        return $this->idControlbytype;
    }

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