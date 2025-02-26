<?php

namespace App\Entity;

use App\Repository\AnalysisfileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Analysisfile
 */
#[ORM\Table(name: 'analysisfile')]
#[ORM\UniqueConstraint(name: 'id_analysisfile', columns: ['id_analysisfile'])]
#[ORM\Entity(repositoryClass: AnalysisfileRepository::class)]
class Analysisfile
{
    #[ORM\Column(name: 'id_analysisfile', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idAnalysisfile = null;

    #[ORM\Column(name: 'name_analysisfile', type: 'string', length: 250, nullable: false)]
    private ?string $nameAnalysisfile = null;

    public function getIdAnalysisfile(): ?string
    {
        return $this->idAnalysisfile;
    }

    public function getNameAnalysisfile(): ?string
    {
        return $this->nameAnalysisfile;
    }

    public function setNameAnalysisfile(string $nameAnalysisfile): self
    {
        $this->nameAnalysisfile = $nameAnalysisfile;

        return $this;
    }
}