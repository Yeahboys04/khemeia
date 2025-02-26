<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Dangernote
 */
#[ORM\Table(name: 'dangernote')]
#[ORM\UniqueConstraint(name: 'id_dangerNote', columns: ['id_dangerNote'])]
#[ORM\UniqueConstraint(name: 'name_dangerNote', columns: ['name_dangerNote'])]
#[ORM\Entity]
class Dangernote
{
    #[ORM\Column(name: 'id_dangerNote', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idDangernote = null;

    #[ORM\Column(name: 'name_dangerNote', type: 'string', length: 50, nullable: false)]
    private ?string $nameDangernote = null;

    #[ORM\Column(name: 'sentenceDangerNote', type: 'string', length: 500, nullable: false)]
    private ?string $sentencedangernote = null;

    /**
     * @var Collection<int, Chimicalproduct>
     */
    #[ORM\ManyToMany(targetEntity: Chimicalproduct::class, mappedBy: 'idDangernote')]
    private Collection $idChimicalproduct;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idChimicalproduct = new ArrayCollection();
    }

    public function getIdDangernote(): ?string
    {
        return $this->idDangernote;
    }

    public function getNameDangernote(): ?string
    {
        return $this->nameDangernote;
    }

    public function setNameDangernote(string $nameDangernote): self
    {
        $this->nameDangernote = $nameDangernote;

        return $this;
    }

    public function getSentencedangernote(): ?string
    {
        return $this->sentencedangernote;
    }

    public function setSentencedangernote(string $sentencedangernote): self
    {
        $this->sentencedangernote = $sentencedangernote;

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
            $idChimicalproduct->addIdDangernote($this);
        }

        return $this;
    }

    public function removeIdChimicalproduct(Chimicalproduct $idChimicalproduct): self
    {
        if ($this->idChimicalproduct->removeElement($idChimicalproduct)) {
            $idChimicalproduct->removeIdDangernote($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameDangernote . ": " . $this->sentencedangernote;
    }
}