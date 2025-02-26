<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Supplier
 */
#[ORM\Table(name: 'supplier')]
#[ORM\UniqueConstraint(name: 'id_supplier', columns: ['id_supplier'])]
#[ORM\Entity]
class Supplier
{
    #[ORM\Column(name: 'id_supplier', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idSupplier = null;

    #[ORM\Column(name: 'name_supplier', type: 'string', length: 250, nullable: false)]
    private ?string $nameSupplier = null;

    public function getIdSupplier(): ?string
    {
        return $this->idSupplier;
    }

    public function getNameSupplier(): ?string
    {
        return $this->nameSupplier;
    }

    public function setNameSupplier(string $nameSupplier): self
    {
        $this->nameSupplier = $nameSupplier;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameSupplier;
    }
}