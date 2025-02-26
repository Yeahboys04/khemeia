<?php

namespace App\Entity;

use App\Repository\SecurityfileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Securityfile
 */
#[ORM\Table(name: 'securityfile')]
#[ORM\UniqueConstraint(name: 'id_securityfile', columns: ['id_securityfile'])]
#[ORM\Entity(repositoryClass: SecurityfileRepository::class)]
class Securityfile
{
    #[ORM\Column(name: 'id_securityfile', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idSecurityfile = null;

    #[ORM\Column(name: 'name_securityfile', type: 'string', length: 250, nullable: false)]
    private ?string $nameSecurityfile = null;

    public function getIdSecurityfile(): ?string
    {
        return $this->idSecurityfile;
    }

    public function getNameSecurityfile(): ?string
    {
        return $this->nameSecurityfile;
    }

    public function setNameSecurityfile(string $nameSecurityfile): self
    {
        $this->nameSecurityfile = $nameSecurityfile;

        return $this;
    }
}