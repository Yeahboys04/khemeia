<?php

namespace App\Entity;

use App\Repository\StoragecardRepository;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

/**
 * Storagecard
 */
#[ORM\Table(name: 'storagecard')]
#[ORM\UniqueConstraint(name: 'id_storageCard', columns: ['id_storageCard'])]
#[ORM\Index(columns: ['id_analysisfile'], name: 'FK_storageCard_analysisfile')]
#[ORM\Index(columns: ['id_chimicalProduct'], name: 'FK_storageCard_chimicalProduct')]
#[ORM\Index(columns: ['id_property'], name: 'FK_storageCard_property')]
#[ORM\Index(columns: ['id_securityfile'], name: 'FK_storageCard_securityfile')]
#[ORM\Index(columns: ['id_shelvingUnit'], name: 'FK_storageCard_shelvingUnit')]
#[ORM\Index(columns: ['id_supplier'], name: 'FK_storageCard_supplier')]
#[ORM\Entity(repositoryClass: StoragecardRepository::class)]
class Storagecard
{
    #[ORM\Column(name: 'id_storageCard', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idStoragecard = null;

    #[ORM\Column(name: 'stockQuantity',type: 'decimal', precision: 11, scale: 2, nullable: true)]
    private ?float $stockquantity = null;

    #[ORM\Column(name: 'capacity', type: 'decimal', precision: 11, scale: 2)]
    private ?float $capacity = null;

    #[ORM\Column(name: 'purity', type: 'string', length: 50, nullable: true)]
    private ?string $purity = null;

    #[ORM\Column(name: 'serialNumber', type: 'string', length: 50, nullable: true)]
    private ?string $serialnumber = null;

    #[ORM\Column(name: 'openDate', type: 'date', nullable: true)]
    private ?\DateTimeInterface $opendate = null;

    #[ORM\Column(name: 'expirationDate', type: 'date', nullable: true)]
    private ?\DateTimeInterface $expirationdate = null;

    #[ORM\Column(name: 'temperature', type: 'string', length: 50, nullable: true)]
    private ?string $temperature = null;

    #[ORM\Column(name: 'reference', type: 'string', length: 100, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(name: 'isArchived', type: 'boolean', nullable: false)]
    private ?bool $isarchived = null;

    #[ORM\Column(name: 'isRisked', type: 'boolean', nullable: false)]
    private ?bool $isrisked = null;

    #[ORM\Column(name: 'isPublished', type: 'boolean', nullable: false)]
    private ?bool $ispublished = null;

    #[ORM\Column(name: 'commentary', type: 'text', nullable: true)]
    private ?string $commentary = null;

    #[ORM\ManyToOne(targetEntity: Analysisfile::class)]
    #[ORM\JoinColumn(name: 'id_analysisfile', referencedColumnName: 'id_analysisfile')]
    private ?Analysisfile $idAnalysisfile = null;

    #[ORM\ManyToOne(targetEntity: Chimicalproduct::class)]
    #[ORM\JoinColumn(name: 'id_chimicalProduct', referencedColumnName: 'id_chimicalProduct')]
    private ?Chimicalproduct $idChimicalproduct = null;

    #[ORM\ManyToOne(targetEntity: Property::class)]
    #[ORM\JoinColumn(name: 'id_property', referencedColumnName: 'id_property')]
    private ?Property $idProperty = null;

    #[ORM\ManyToOne(targetEntity: Securityfile::class)]
    #[ORM\JoinColumn(name: 'id_securityfile', referencedColumnName: 'id_securityfile')]
    private ?Securityfile $idSecurityfile = null;

    #[ORM\ManyToOne(targetEntity: Shelvingunit::class)]
    #[ORM\JoinColumn(name: 'id_shelvingUnit', referencedColumnName: 'id_shelvingUnit')]
    private ?Shelvingunit $idShelvingunit = null;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(name: 'id_supplier', referencedColumnName: 'id_supplier')]
    private ?Supplier $idSupplier = null;

    #[ORM\Column(name: 'state_type', type: 'string', length: 10, nullable: true)]
    private ?string $stateType = null;

    #[ORM\Column(name: 'creation_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $creationDate = null;


    public function getIdStoragecard(): ?string
    {
        return $this->idStoragecard;
    }

    public function getStockquantity(): ?float
    {
        return $this->stockquantity;
    }

    public function setStockquantity(?float $stockquantity): self
    {
        $this->stockquantity = $stockquantity;

        return $this;
    }

    public function getCapacity(): ?float
    {
        return $this->capacity;
    }

    public function setCapacity(?float $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getPurity(): ?string
    {
        return $this->purity;
    }

    public function setPurity(?string $purity): self
    {
        $this->purity = $purity;

        return $this;
    }

    public function getSerialnumber(): ?string
    {
        return $this->serialnumber;
    }

    public function setSerialnumber(?string $serialnumber): self
    {
        $this->serialnumber = $serialnumber;

        return $this;
    }

    public function getOpendate(): ?\DateTimeInterface
    {
        return $this->opendate;
    }

    public function setOpendate(?\DateTimeInterface $opendate): self
    {
        $this->opendate = $opendate;

        return $this;
    }

    public function getExpirationdate(): ?\DateTimeInterface
    {
        return $this->expirationdate;
    }

    public function setExpirationdate(?\DateTimeInterface $expirationdate): self
    {
        $this->expirationdate = $expirationdate;

        return $this;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): self
    {
        if ($temperature === null || empty($temperature)) {
            $this->temperature = $temperature;
            return $this;
        }

        $regex = "#^((-?)[0-9]{1,2})( - ((-?)[0-9]{1,2}))?$#";
        if (preg_match_all($regex, $temperature)) {
            if (preg_match("# - #", $temperature)) {
                $values = explode(" - ", $temperature);
                if (intval($values[0]) < intval($values[1])) {
                    $this->temperature = $temperature;

                    return $this;
                } else {
                    throw new LogicException("La température \"" .
                        $temperature . "\" n'est pas valide. Merci de respecter"
                        . " ce format: \"XX - XX\" (espaces compris, nombres négatifs autorisés) ou \"XX\".");
                }
            }
            $this->temperature = $temperature;

            return $this;
        }
        throw new LogicException("La température \"" .
            $temperature . "\" n'est pas valide. Merci de respecter"
            ." ce format: \"XX - XX\" (espaces compris, nombres négatifs autorisés) ou \"XX\".");
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getIsarchived(): ?bool
    {
        return $this->isarchived;
    }

    public function setIsarchived(bool $isarchived): self
    {
        $this->isarchived = $isarchived;

        return $this;
    }

    public function getIsrisked(): ?bool
    {
        return $this->isrisked;
    }

    public function setIsrisked(bool $isrisked): self
    {
        $this->isrisked = $isrisked;

        return $this;
    }

    public function getIspublished(): ?bool
    {
        return $this->ispublished;
    }

    public function setIspublished(bool $ispublished): self
    {
        $this->ispublished = $ispublished;

        return $this;
    }

    public function getCommentary(): ?string
    {
        return $this->commentary;
    }

    public function setCommentary(?string $commentary): void
    {
        $this->commentary = $commentary;
    }

    public function getIdAnalysisfile(): ?Analysisfile
    {
        return $this->idAnalysisfile;
    }

    public function setIdAnalysisfile(?Analysisfile $idAnalysisfile): self
    {
        $this->idAnalysisfile = $idAnalysisfile;

        return $this;
    }

    public function getIdChimicalproduct(): ?Chimicalproduct
    {
        return $this->idChimicalproduct;
    }

    public function setIdChimicalproduct(?Chimicalproduct $idChimicalproduct): self
    {
        $this->idChimicalproduct = $idChimicalproduct;

        return $this;
    }

    public function getIdProperty(): ?Property
    {
        return $this->idProperty;
    }

    public function setIdProperty(?Property $idProperty): self
    {
        $this->idProperty = $idProperty;

        return $this;
    }

    public function getIdSecurityfile(): ?Securityfile
    {
        return $this->idSecurityfile;
    }

    public function setIdSecurityfile(?Securityfile $idSecurityfile): self
    {
        $this->idSecurityfile = $idSecurityfile;

        return $this;
    }

    public function getIdShelvingunit(): ?Shelvingunit
    {
        return $this->idShelvingunit;
    }

    public function setIdShelvingunit(?Shelvingunit $idShelvingunit): self
    {
        $this->idShelvingunit = $idShelvingunit;

        return $this;
    }

    public function getIdSupplier(): ?Supplier
    {
        return $this->idSupplier;
    }

    public function setIdSupplier(?Supplier $idSupplier): self
    {
        $this->idSupplier = $idSupplier;

        return $this;
    }

    public function __toString(): string
    {
        return "Fiche de stockage du produit : " . $this->idChimicalproduct . " - " . $this->idShelvingunit;
    }

    public function getStateType(): ?string
    {
        return $this->stateType;
    }

    public function setStateType(?string $stateType): self
    {
        $this->stateType = $stateType;
        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;
        return $this;
    }
}