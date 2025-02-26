<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cautionaryadvice
 */
#[ORM\Table(name: 'cautionaryadvice')]
#[ORM\UniqueConstraint(name: 'id_cautionaryAdvice', columns: ['id_cautionaryAdvice'])]
#[ORM\UniqueConstraint(name: 'name_cautionaryAdvice', columns: ['name_cautionaryAdvice'])]
#[ORM\Entity]
class Cautionaryadvice
{
    #[ORM\Column(name: 'id_cautionaryAdvice', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idCautionaryadvice = null;

    #[ORM\Column(name: 'name_cautionaryAdvice', type: 'string', length: 50, nullable: false)]
    private ?string $nameCautionaryadvice = null;

    #[ORM\Column(name: 'sentenceCautionaryAdvice', type: 'string', length: 500, nullable: false)]
    private ?string $sentencecautionaryadvice = null;

    /**
     * @var Collection<int, Chimicalproduct>
     */
    #[ORM\ManyToMany(targetEntity: Chimicalproduct::class, mappedBy: 'idCautionaryadvice')]
    private Collection $idChimicalproduct;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idChimicalproduct = new ArrayCollection();
    }

    public function getIdCautionaryadvice(): ?string
    {
        return $this->idCautionaryadvice;
    }

    public function getNameCautionaryadvice(): ?string
    {
        return $this->nameCautionaryadvice;
    }

    public function setNameCautionaryadvice(string $nameCautionaryadvice): self
    {
        $this->nameCautionaryadvice = $nameCautionaryadvice;

        return $this;
    }

    public function getSentencecautionaryadvice(): ?string
    {
        return $this->sentencecautionaryadvice;
    }

    public function setSentencecautionaryadvice(string $sentencecautionaryadvice): self
    {
        $this->sentencecautionaryadvice = $sentencecautionaryadvice;

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
            $idChimicalproduct->addIdCautionaryadvice($this);
        }

        return $this;
    }

    public function removeIdChimicalproduct(Chimicalproduct $idChimicalproduct): self
    {
        if ($this->idChimicalproduct->removeElement($idChimicalproduct)) {
            $idChimicalproduct->removeIdCautionaryadvice($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameCautionaryadvice . ": " . $this->sentencecautionaryadvice;
    }
}