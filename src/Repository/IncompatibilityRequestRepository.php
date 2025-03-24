<?php

namespace App\Repository;

use App\Entity\IncompatibilityRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IncompatibilityRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncompatibilityRequest::class);
    }

    /**
     * Trouve les demandes en attente (status = pending)
     *
     * @return IncompatibilityRequest[]
     */
    public function findPendingRequests(): array
    {
        return $this->createQueryBuilder('ir')
            ->where('ir.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('ir.requestDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les demandes traitées (status = approved ou rejected)
     *
     * @param int $limit Nombre maximum de résultats à retourner
     * @return IncompatibilityRequest[]
     */
    public function findProcessedRequests(int $limit = 20): array
    {
        return $this->createQueryBuilder('ir')
            ->where('ir.status IN (:statuses)')
            ->setParameter('statuses', ['approved', 'rejected'])
            ->orderBy('ir.responseDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}