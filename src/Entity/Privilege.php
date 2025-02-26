<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Privilege
 */
#[ORM\Table(name: 'privilege')]
#[ORM\UniqueConstraint(name: 'id_privilege', columns: ['id_privilege'])]
#[ORM\UniqueConstraint(name: 'keyPrivilege', columns: ['keyPrivilege'])]
#[ORM\Entity]
class Privilege
{
    #[ORM\Column(name: 'id_privilege', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idPrivilege = null;

    #[ORM\Column(name: 'keyPrivilege', type: 'string', length: 50, nullable: false)]
    private ?string $keyprivilege = null;

    /**
     * @var Collection<int, Status>
     */
    #[ORM\ManyToMany(targetEntity: Status::class, mappedBy: 'idPrivilege')]
    private Collection $idStatus;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idStatus = new ArrayCollection();
    }

    public function getIdPrivilege(): ?string
    {
        return $this->idPrivilege;
    }

    public function getKeyprivilege(): ?string
    {
        return $this->keyprivilege;
    }

    public function setKeyprivilege(string $keyprivilege): self
    {
        $this->keyprivilege = $keyprivilege;

        return $this;
    }

    /**
     * @return Collection<int, Status>
     */
    public function getIdStatus(): Collection
    {
        return $this->idStatus;
    }

    public function addIdStatus(Status $idStatus): self
    {
        if (!$this->idStatus->contains($idStatus)) {
            $this->idStatus->add($idStatus);
            $idStatus->addIdPrivilege($this);
        }

        return $this;
    }

    public function removeIdStatus(Status $idStatus): self
    {
        if ($this->idStatus->removeElement($idStatus)) {
            $idStatus->removeIdPrivilege($this);
        }

        return $this;
    }
}