<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stock
 */
#[ORM\Table(name: 'stock')]
#[ORM\UniqueConstraint(name: 'id_stock', columns: ['id_stock'])]
#[ORM\Index(name: 'FK_stock_site', columns: ['id_site'])]
#[ORM\Entity]
class Stock
{
    #[ORM\Column(name: 'id_stock', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idStock = null;

    #[ORM\Column(name: 'name_stock', type: 'string', length: 100, nullable: false)]
    private ?string $nameStock = null;

    #[ORM\ManyToOne(targetEntity: Site::class)]
    #[ORM\JoinColumn(name: 'id_site', referencedColumnName: 'id_site')]
    private ?Site $idSite = null;

    public function getIdStock(): ?string
    {
        return $this->idStock;
    }

    public function getNameStock(): ?string
    {
        return $this->nameStock;
    }

    public function setNameStock(string $nameStock): self
    {
        $this->nameStock = $nameStock;

        return $this;
    }

    public function getIdSite(): ?Site
    {
        return $this->idSite;
    }

    public function setIdSite(?Site $idSite): self
    {
        $this->idSite = $idSite;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameStock;
    }
}