<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document
 */
#[ORM\Table(name: 'document')]
#[ORM\UniqueConstraint(name: 'id_document', columns: ['id_document'])]
#[ORM\Entity]
class Document
{
    #[ORM\Column(name: 'id_document', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idDocument = null;

    #[ORM\Column(name: 'name_document', type: 'string', length: 250, nullable: false)]
    private ?string $nameDocument = null;

    public function getIdDocument(): ?string
    {
        return $this->idDocument;
    }

    public function getNameDocument(): ?string
    {
        return $this->nameDocument;
    }

    public function setNameDocument(string $nameDocument): self
    {
        $this->nameDocument = $nameDocument;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nameDocument;
    }
}