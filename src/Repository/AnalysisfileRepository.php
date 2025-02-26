<?php

namespace App\Repository;

use App\Entity\Analysisfile;
use App\Entity\Storagecard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AnalysisfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Analysisfile::class);
    }

    /**
     * Find all analysis files that are not associated with any storage card
     *
     * @return Analysisfile[]
     */
    public function findAllByNullAnalysisFile(): array
    {
        $queryBuilder = $this->createQueryBuilder('af')
            ->leftJoin('App\Entity\Storagecard', 'sc', 'WITH', 'af.idAnalysisfile = sc.idAnalysisfile')
            ->where('sc.idAnalysisfile IS NULL');

        return $queryBuilder->getQuery()->getResult();
    }
}