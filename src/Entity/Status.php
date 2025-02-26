<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Status
 */
#[ORM\Table(name: 'status')]
#[ORM\UniqueConstraint(name: 'id_status', columns: ['id_status'])]
#[ORM\UniqueConstraint(name: 'name_status', columns: ['name_status'])]
#[ORM\UniqueConstraint(name: 'role_status', columns: ['role_status'])]
#[ORM\Entity]
class Status
{
    #[ORM\Column(name: 'id_status', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idStatus = null;

    #[ORM\Column(name: 'name_status', type: 'string', length: 50, nullable: false)]
    private ?string $nameStatus = null;

    #[ORM\Column(name: 'role_status', type: 'string', length: 50, nullable: false)]
    private ?string $roleStatus = null;

    /**
     * @var Collection<int, Privilege>
     */
    #[ORM\ManyToMany(targetEntity: Privilege::class, inversedBy: 'idStatus')]
    #[ORM\JoinTable(name: 'acl')]
    #[ORM\JoinColumn(name: 'id_status', referencedColumnName: 'id_status')]
    #[ORM\InverseJoinColumn(name: 'id_privilege', referencedColumnName: 'id_privilege')]
    private Collection $idPrivilege;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idPrivilege = new ArrayCollection();
    }

    public function getIdStatus(): ?string
    {
        return $this->idStatus;
    }

    public function getNameStatus(): ?string
    {
        return $this->nameStatus;
    }

    public function setNameStatus(string $nameStatus): self
    {
        $this->nameStatus = $nameStatus;

        return $this;
    }

    public function getRoleStatus(): ?string
    {
        return $this->roleStatus;
    }

    public function setRoleStatus(string $roleStatus): self
    {
        $this->roleStatus = $roleStatus;

        return $this;
    }

    /**
     * @return Collection<int, Privilege>
     */
    public function getIdPrivilege(): Collection
    {
        return $this->idPrivilege;
    }

    public function addIdPrivilege(Privilege $idPrivilege): self
    {
        if (!$this->idPrivilege->contains($idPrivilege)) {
            $this->idPrivilege->add($idPrivilege);
        }

        return $this;
    }

    public function removeIdPrivilege(Privilege $idPrivilege): self
    {
        $this->idPrivilege->removeElement($idPrivilege);

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameStatus;
    }
}