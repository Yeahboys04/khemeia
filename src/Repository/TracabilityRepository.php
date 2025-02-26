<?php

namespace App\Repository;

use App\Entity\Tracability;
use App\Entity\User;
use App\Entity\Storagecard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TracabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tracability::class);
    }

    /**
     * Find all tracability records for a specific user
     *
     * @param User|int $user The user entity or its ID
     * @return Tracability[]
     */
    public function findTracabilityByUser($user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.idUser = :query')
            ->setParameter('query', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all tracability records for a specific storage card
     *
     * @param Storagecard|int $idStoragecard The storage card entity or its ID
     * @return Tracability[]
     */
    public function findAllByStoragecard($idStoragecard): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.idStoragecard = :query')
            ->setParameter(':query', $idStoragecard)
            ->getQuery()
            ->getResult();
    }
}