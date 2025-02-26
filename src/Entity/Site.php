<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Site
 */
#[ORM\Table(name: 'site')]
#[ORM\UniqueConstraint(name: 'id_site', columns: ['id_site'])]
#[ORM\UniqueConstraint(name: 'name_site', columns: ['name_site'])]
#[ORM\Entity]
class Site
{
    #[ORM\Column(name: 'id_site', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idSite = null;

    #[ORM\Column(name: 'name_site', type: 'string', length: 50, nullable: false)]
    private ?string $nameSite = null;

    #[ORM\Column(name: 'fullnameSupervisor', type: 'string', length: 250, nullable: false)]
    private ?string $fullnamesupervisor = null;

    #[ORM\Column(name: 'phoneNumber', type: 'string', length: 10, nullable: false, options: ['fixed' => true])]
    private ?string $phonenumber = null;

    public function getIdSite(): ?string
    {
        return $this->idSite;
    }

    public function getNameSite(): ?string
    {
        return $this->nameSite;
    }

    public function setNameSite(string $nameSite): self
    {
        $this->nameSite = $nameSite;

        return $this;
    }

    public function getFullnamesupervisor(): ?string
    {
        return $this->fullnamesupervisor;
    }

    public function setFullnamesupervisor(string $fullnamesupervisor): self
    {
        $this->fullnamesupervisor = $fullnamesupervisor;

        return $this;
    }

    public function getPhonenumber(): ?string
    {
        return $this->phonenumber;
    }

    public function setPhonenumber(string $phonenumber): self
    {
        $this->phonenumber = $phonenumber;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameSite;
    }
}