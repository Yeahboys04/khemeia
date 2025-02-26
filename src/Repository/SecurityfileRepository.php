<?php

namespace App\Repository;

use App\Entity\Securityfile;
use App\Entity\Storagecard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SecurityfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Securityfile::class);
    }

    /**
     * Find all security files that are not associated with any storage card
     *
     * @return Securityfile[]
     */
    public function findAllByNullSecurityFile(): array
    {
        return $this->createQueryBuilder('sf')
            ->leftJoin('App\Entity\Storagecard', 'sc', 'WITH', 'sf.idSecurityfile = sc.idSecurityfile')
            ->where('sc.idSecurityfile IS NULL')
            ->getQuery()
            ->getResult();
    }
}