<?php

namespace App\Repository;

use App\Entity\Movedhistory;
use App\Entity\Storagecard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MovedhistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movedhistory::class);
    }

    /**
     * Find all moved history records for a specific storage card
     *
     * @param Storagecard|int $idStoragecard The storage card entity or its ID
     * @return Movedhistory[]
     */
    public function findAllByStoragecard($idStoragecard): array
    {
        return $this->createQueryBuilder('mh')
            ->where('mh.idStoragecard = :query')
            ->setParameter(':query', $idStoragecard)
            ->getQuery()
            ->getResult();
    }
}