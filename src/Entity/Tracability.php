<?php

namespace App\Entity;

use App\Repository\TracabilityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tracability
 */
#[ORM\Table(name: 'tracability')]
#[ORM\UniqueConstraint(name: 'id_tracability', columns: ['id_tracability'])]
#[ORM\Index(name: 'FK_tracability_storageCard', columns: ['id_storageCard'])]
#[ORM\Index(name: 'FK_tracability_user', columns: ['id_user'])]
#[ORM\Entity(repositoryClass: TracabilityRepository::class)]
class Tracability
{
    #[ORM\Column(name: 'id_tracability', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idTracability = null;

    #[ORM\Column(name: 'retireDate', type: 'date', nullable: false)]
    private ?\DateTimeInterface $retiredate = null;

    #[ORM\Column(name: 'retireQuantity', type: 'integer', nullable: true)]
    private ?int $retirequantity = null;

    #[ORM\ManyToOne(targetEntity: Storagecard::class)]
    #[ORM\JoinColumn(name: 'id_storageCard', referencedColumnName: 'id_storageCard')]
    private ?Storagecard $idStoragecard = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user')]
    private ?User $idUser = null;

    public function getIdTracability(): ?string
    {
        return $this->idTracability;
    }

    public function getRetiredate(): ?\DateTimeInterface
    {
        return $this->retiredate;
    }

    public function setRetiredate(\DateTimeInterface $retiredate): self
    {
        $this->retiredate = $retiredate;

        return $this;
    }

    public function getRetirequantity(): ?int
    {
        return $this->retirequantity;
    }

    public function setRetirequantity(?int $retirequantity): self
    {
        $this->retirequantity = $retirequantity;

        return $this;
    }

    public function getIdStoragecard(): ?Storagecard
    {
        return $this->idStoragecard;
    }

    public function setIdStoragecard(?Storagecard $idStoragecard): self
    {
        $this->idStoragecard = $idStoragecard;

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }
}