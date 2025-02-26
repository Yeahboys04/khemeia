<?php

namespace App\Entity;

use App\Repository\MovedhistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Movedhistory
 */
#[ORM\Table(name: 'movedhistory')]
#[ORM\UniqueConstraint(name: 'id_movedhistory', columns: ['id_movedhistory'])]
#[ORM\Index(name: 'FK_movedhistory_shelvingunit', columns: ['id_shelvingunit'])]
#[ORM\Index(name: 'FK_movedhistory_storagecard', columns: ['id_storagecard'])]
#[ORM\Index(name: 'KF_movedhistory_user', columns: ['id_user'])]
#[ORM\Entity(repositoryClass: MovedhistoryRepository::class)]
class Movedhistory
{
    #[ORM\Column(name: 'id_movedhistory', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idMovedhistory = null;

    #[ORM\Column(name: 'movedate', type: 'date', nullable: false)]
    private ?\DateTimeInterface $movedate = null;

    #[ORM\ManyToOne(targetEntity: Shelvingunit::class)]
    #[ORM\JoinColumn(name: 'id_shelvingunit', referencedColumnName: 'id_shelvingUnit')]
    private ?Shelvingunit $idShelvingunit = null;

    #[ORM\ManyToOne(targetEntity: Storagecard::class)]
    #[ORM\JoinColumn(name: 'id_storagecard', referencedColumnName: 'id_storageCard')]
    private ?Storagecard $idStoragecard = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user')]
    private ?User $idUser = null;

    public function getIdMovedhistory(): ?string
    {
        return $this->idMovedhistory;
    }

    public function getMovedate(): ?\DateTimeInterface
    {
        return $this->movedate;
    }

    public function setMovedate(\DateTimeInterface $movedate): self
    {
        $this->movedate = $movedate;

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