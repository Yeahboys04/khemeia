<?php

namespace App\Repository;

use App\Entity\ExternalWithdrawalRequest;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExternalWithdrawalRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalWithdrawalRequest::class);
    }

    /**
     * Trouve les demandes en attente pour un site spécifique
     *
     * @param Site|int $site Le site pour lequel on recherche les demandes
     * @return ExternalWithdrawalRequest[]
     */
    public function findPendingRequestsForSite($site): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.sourceSite = :site')
            ->andWhere('er.status = :status')
            ->setParameter('site', $site)
            ->setParameter('status', 'pending')
            ->orderBy('er.isUrgent', 'DESC')
            ->addOrderBy('er.requestDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les demandes traitées pour un site spécifique
     *
     * @param Site|int $site Le site pour lequel on recherche les demandes
     * @param int $limit Nombre maximum de résultats à retourner
     * @return ExternalWithdrawalRequest[]
     */
    public function findProcessedRequestsForSite($site, int $limit = 20): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.sourceSite = :site')
            ->andWhere('er.status IN (:statuses)')
            ->setParameter('site', $site)
            ->setParameter('statuses', ['approved', 'rejected'])
            ->orderBy('er.responseDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les demandes faites par un utilisateur spécifique
     *
     * @param User|int $user L'utilisateur qui a fait les demandes
     * @return ExternalWithdrawalRequest[]
     */
    public function findRequestsByUser($user): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.requester = :user')
            ->setParameter('user', $user)
            ->orderBy('er.requestDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les demandes en attente faites par un utilisateur spécifique
     *
     * @param User|int $user L'utilisateur qui a fait les demandes
     * @return ExternalWithdrawalRequest[]
     */
    public function findPendingRequestsByUser($user): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.requester = :user')
            ->andWhere('er.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->orderBy('er.requestDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les demandes approuvées faites par un utilisateur spécifique
     * qui n'ont pas encore été complétées
     *
     * @param User|int $user L'utilisateur qui a fait les demandes
     * @return ExternalWithdrawalRequest[]
     */
    public function findApprovedUncompletedRequestsByUser($user): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.requester = :user')
            ->andWhere('er.status = :status')
            ->andWhere('er.isCompleted = :completed')
            ->setParameter('user', $user)
            ->setParameter('status', 'approved')
            ->setParameter('completed', false)
            ->orderBy('er.responseDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}