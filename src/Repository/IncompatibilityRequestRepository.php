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

    /**
     * Trouve les demandes approuvées qui n'ont pas encore été utilisées
     *
     * @param User|int $user L'utilisateur concerné
     * @return IncompatibilityRequest[]
     */
    public function findApprovedUnusedRequests($user): array
    {
        return $this->createQueryBuilder('ir')
            ->where('ir.requester = :user')
            ->andWhere('ir.status = :status')
            ->andWhere('ir.isUsed = :isUsed')
            ->setParameter('user', $user)
            ->setParameter('status', 'approved')
            ->setParameter('isUsed', false)
            ->orderBy('ir.responseDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}