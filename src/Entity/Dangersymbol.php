<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Dangersymbol
 */
#[ORM\Table(name: 'dangersymbol')]
#[ORM\UniqueConstraint(name: 'id_dangerSymbol', columns: ['id_dangerSymbol'])]
#[ORM\UniqueConstraint(name: 'name_dangerSymbol', columns: ['name_dangerSymbol'])]
#[ORM\Entity]
class Dangersymbol
{
    #[ORM\Column(name: 'id_dangerSymbol', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idDangersymbol = null;

    #[ORM\Column(name: 'name_dangerSymbol', type: 'string', length: 5, nullable: false, options: ['fixed' => true])]
    private ?string $nameDangersymbol = null;

    #[ORM\Column(name: 'description_dangerSymbol', type: 'string', length: 250, nullable: false)]
    private ?string $descriptionDangersymbol = null;

    #[ORM\Column(name: 'icon', type: 'string', length: 100, nullable: false)]
    private ?string $icon = null;

    /**
     * @var Collection<int, Chimicalproduct>
     */
    #[ORM\ManyToMany(targetEntity: Chimicalproduct::class, mappedBy: 'idDangersymbol')]
    private Collection $idChimicalproduct;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idChimicalproduct = new ArrayCollection();
    }

    public function getIdDangersymbol(): ?string
    {
        return $this->idDangersymbol;
    }

    public function getNameDangersymbol(): ?string
    {
        return $this->nameDangersymbol;
    }

    public function setNameDangersymbol(string $nameDangersymbol): self
    {
        $this->nameDangersymbol = $nameDangersymbol;

        return $this;
    }

    public function getDescriptionDangersymbol(): ?string
    {
        return $this->descriptionDangersymbol;
    }

    public function setDescriptionDangersymbol(string $descriptionDangersymbol): self
    {
        $this->descriptionDangersymbol = $descriptionDangersymbol;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, Chimicalproduct>
     */
    public function getIdChimicalproduct(): Collection
    {
        return $this->idChimicalproduct;
    }

    public function addIdChimicalproduct(Chimicalproduct $idChimicalproduct): self
    {
        if (!$this->idChimicalproduct->contains($idChimicalproduct)) {
            $this->idChimicalproduct->add($idChimicalproduct);
            $idChimicalproduct->addIdDangersymbol($this);
        }

        return $this;
    }

    public function removeIdChimicalproduct(Chimicalproduct $idChimicalproduct): self
    {
        if ($this->idChimicalproduct->removeElement($idChimicalproduct)) {
            $idChimicalproduct->removeIdDangersymbol($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameDangersymbol . ": " . $this->descriptionDangersymbol;
    }
}