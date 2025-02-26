<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Validator\Constraints as Assert;

/**
 * Type
 */
#[ORM\Table(name: 'type')]
#[ORM\UniqueConstraint(name: 'id_type', columns: ['id_type'])]
#[ORM\Entity]
class Type
{
    #[ORM\Column(name: 'id_type', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idType = null;

    #[ORM\Column(name: 'name_type', type: 'string', length: 50, nullable: false)]
    private ?string $nameType = null;

    /**
     * @var Collection<int, Type>
     */
    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: 'idType1')]
    #[ORM\JoinTable(name: 'controlbytype')]
    #[ORM\JoinColumn(name: 'id_type1', referencedColumnName: 'id_type')]
    #[ORM\InverseJoinColumn(name: 'id_type2', referencedColumnName: 'id_type')]
    private Collection $idType2;

    /**
     * @var Collection<int, Chimicalproduct>
     */
    #[ORM\ManyToMany(targetEntity: Chimicalproduct::class, inversedBy: 'idType')]
    #[ORM\JoinTable(name: 'productbytype')]
    #[ORM\JoinColumn(name: 'id_type', referencedColumnName: 'id_type')]
    #[ORM\InverseJoinColumn(name: 'id_chimicalProduct', referencedColumnName: 'id_chimicalProduct')]
    private Collection $idChimicalproduct;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idType2 = new ArrayCollection();
        $this->idChimicalproduct = new ArrayCollection();
    }

    public function getIdType(): ?string
    {
        return $this->idType;
    }

    public function getNameType(): ?string
    {
        return $this->nameType;
    }

    public function setNameType(string $nameType): self
    {
        $this->nameType = $nameType;

        return $this;
    }

    /**
     * @return Collection<int, Type>
     */
    public function getIdType2(): Collection
    {
        return $this->idType2;
    }

    public function addIdType2(Type $idType2): self
    {
        if (!$this->idType2->contains($idType2)) {
            $this->idType2->add($idType2);
        }

        return $this;
    }

    public function removeIdType2(Type $idType2): self
    {
        $this->idType2->removeElement($idType2);

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
        }

        return $this;
    }

    public function removeIdChimicalproduct(Chimicalproduct $idChimicalproduct): self
    {
        $this->idChimicalproduct->removeElement($idChimicalproduct);

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameType;
    }
}