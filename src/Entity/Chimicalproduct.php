<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Chimicalproduct
 */
#[ORM\Table(name: 'chimicalproduct')]
#[ORM\UniqueConstraint(name: 'id_chimicalProduct', columns: ['id_chimicalProduct'])]
#[ORM\Entity]
class Chimicalproduct
{
    #[ORM\Column(name: 'id_chimicalProduct', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idChimicalproduct = null;

    #[ORM\Column(name: 'name_chimicalProduct', type: 'string', length: 250, nullable: false)]
    private ?string $nameChimicalproduct = null;

    #[ORM\Column(name: 'formula', type: 'string', length: 250, nullable: true)]
    private ?string $formula = null;

    #[ORM\Column(name: 'CASNumber', type: 'string', length: 25, nullable: true)]
    private ?string $casnumber = null;

    #[ORM\Column(name: 'isCMR', type: 'boolean', nullable: false)]
    private ?bool $iscmr = null;

    #[ORM\Column(name: 'solvent', type: 'string', length: 250, nullable: true)]
    private ?string $solvent = null;

    /**
     * @var Collection<int, Cautionaryadvice>
     */
    #[ORM\ManyToMany(targetEntity: Cautionaryadvice::class, inversedBy: 'idChimicalproduct')]
    #[ORM\JoinTable(name: 'productbycautionaryadvice')]
    #[ORM\JoinColumn(name: 'id_chimicalProduct', referencedColumnName: 'id_chimicalProduct')]
    #[ORM\InverseJoinColumn(name: 'id_cautionaryAdvice', referencedColumnName: 'id_cautionaryAdvice')]
    private Collection $idCautionaryadvice;

    /**
     * @var Collection<int, Dangernote>
     */
    #[ORM\ManyToMany(targetEntity: Dangernote::class, inversedBy: 'idChimicalproduct')]
    #[ORM\JoinTable(name: 'productbydangernote')]
    #[ORM\JoinColumn(name: 'id_chimicalProduct', referencedColumnName: 'id_chimicalProduct')]
    #[ORM\InverseJoinColumn(name: 'id_dangerNote', referencedColumnName: 'id_dangerNote')]
    private Collection $idDangernote;

    /**
     * @var Collection<int, Dangersymbol>
     */
    #[ORM\ManyToMany(targetEntity: Dangersymbol::class, inversedBy: 'idChimicalproduct')]
    #[ORM\JoinTable(name: 'productbydangersymbol')]
    #[ORM\JoinColumn(name: 'id_chimicalProduct', referencedColumnName: 'id_chimicalProduct')]
    #[ORM\InverseJoinColumn(name: 'id_dangerSymbol', referencedColumnName: 'id_dangerSymbol')]
    private Collection $idDangersymbol;

    /**
     * @var Collection<int, Type>
     */
    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: 'idChimicalproduct')]
    #[ORM\JoinTable(name: 'productbytype')]
    #[ORM\JoinColumn(name: 'id_chimicalProduct', referencedColumnName: 'id_chimicalProduct')]
    #[ORM\InverseJoinColumn(name: 'id_type', referencedColumnName: 'id_type')]
    private Collection $idType;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idCautionaryadvice = new ArrayCollection();
        $this->idDangernote = new ArrayCollection();
        $this->idDangersymbol = new ArrayCollection();
        $this->idType = new ArrayCollection();
    }

    public function getIdChimicalproduct(): ?string
    {
        return $this->idChimicalproduct;
    }

    public function getNameChimicalproduct(): ?string
    {
        return $this->nameChimicalproduct;
    }

    public function setNameChimicalproduct(string $nameChimicalproduct): self
    {
        $this->nameChimicalproduct = $nameChimicalproduct;

        return $this;
    }

    public function getFormula(): ?string
    {
        return $this->formula;
    }

    public function setFormula(?string $formula): self
    {
        $this->formula = $formula;

        return $this;
    }

    public function getCasnumber(): ?string
    {
        return $this->casnumber;
    }

    public function setCasnumber(?string $casnumber): self
    {
        $this->casnumber = $casnumber;

        return $this;
    }

    public function getIscmr(): ?bool
    {
        return $this->iscmr;
    }

    public function setIscmr(bool $iscmr): self
    {
        $this->iscmr = $iscmr;

        return $this;
    }

    public function getSolvent(): ?string
    {
        return $this->solvent;
    }

    public function setSolvent(?string $solvent): self
    {
        $this->solvent = $solvent;

        return $this;
    }

    /**
     * @return Collection<int, Cautionaryadvice>
     */
    public function getIdCautionaryadvice(): Collection
    {
        return $this->idCautionaryadvice;
    }

    public function addIdCautionaryadvice(Cautionaryadvice $idCautionaryadvice): self
    {
        if (!$this->idCautionaryadvice->contains($idCautionaryadvice)) {
            $this->idCautionaryadvice->add($idCautionaryadvice);
        }

        return $this;
    }

    public function removeIdCautionaryadvice(Cautionaryadvice $idCautionaryadvice): self
    {
        $this->idCautionaryadvice->removeElement($idCautionaryadvice);

        return $this;
    }

    /**
     * @return Collection<int, Dangernote>
     */
    public function getIdDangernote(): Collection
    {
        return $this->idDangernote;
    }

    public function addIdDangernote(Dangernote $idDangernote): self
    {
        if (!$this->idDangernote->contains($idDangernote)) {
            $this->idDangernote->add($idDangernote);
        }

        return $this;
    }

    public function removeIdDangernote(Dangernote $idDangernote): self
    {
        $this->idDangernote->removeElement($idDangernote);

        return $this;
    }

    /**
     * @return Collection<int, Dangersymbol>
     */
    public function getIdDangersymbol(): Collection
    {
        return $this->idDangersymbol;
    }

    public function addIdDangersymbol(Dangersymbol $idDangersymbol): self
    {
        if (!$this->idDangersymbol->contains($idDangersymbol)) {
            $this->idDangersymbol->add($idDangersymbol);
        }

        return $this;
    }

    public function removeIdDangersymbol(Dangersymbol $idDangersymbol): self
    {
        $this->idDangersymbol->removeElement($idDangersymbol);

        return $this;
    }

    /**
     * @return Collection<int, Type>
     */
    public function getIdType(): Collection
    {
        return $this->idType;
    }

    public function addIdType(Type $idType): self
    {
        if (!$this->idType->contains($idType)) {
            $this->idType->add($idType);
            $idType->addIdChimicalproduct($this);
        }

        return $this;
    }

    public function removeIdType(Type $idType): self
    {
        if ($this->idType->removeElement($idType)) {
            $idType->removeIdChimicalproduct($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameChimicalproduct;
    }
}