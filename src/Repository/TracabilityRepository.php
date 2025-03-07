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
            ->orderBy('t.retiredate', 'DESC')
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

    /**
     * Trouve les données de traçabilité filtrées selon les critères
     *
     * @param array $filters Les critères de filtrage
     * @return Tracability[]
     */
    public function findFilteredTracability(array $filters): array
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->innerJoin('t.idStoragecard', 'sc')
            ->innerJoin('sc.idChimicalproduct', 'cp')
            ->leftJoin('t.idUser', 'u');

        // Filtre par utilisateur
        if (!empty($filters['user'])) {
            $queryBuilder->andWhere('t.idUser = :user')
                ->setParameter('user', $filters['user']);
        }

        // Filtre par produit
        if (!empty($filters['product'])) {
            $queryBuilder->andWhere('sc.idChimicalproduct = :product')
                ->setParameter('product', $filters['product']);
        }

        // Filtre par produits CMR
        if (!empty($filters['filterByCMR'])) {
            $queryBuilder->andWhere('cp.iscmr = 1');
        }

        // Filtre par date
        if (isset($filters['dateRangeFilter']) && $filters['dateRangeFilter'] !== 'all') {
            $endDate = new \DateTime();
            $startDate = clone $endDate;

            switch ($filters['dateRangeFilter']) {
                case 'lastMonth':
                    $startDate->modify('-1 month');
                    break;
                case 'last3Months':
                    $startDate->modify('-3 months');
                    break;
                case 'last6Months':
                    $startDate->modify('-6 months');
                    break;
                case 'lastYear':
                    $startDate->modify('-1 year');
                    break;
                case 'custom':
                    if (!empty($filters['startDate'])) {
                        $startDate = $filters['startDate'];
                    }
                    if (!empty($filters['endDate'])) {
                        $endDate = $filters['endDate'];
                        // Ajouter 1 jour à la date de fin pour inclure toute la journée
                        $endDate->modify('+1 day');
                    }
                    break;
            }

            $queryBuilder->andWhere('t.retiredate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        // Tri des résultats
        if (isset($filters['sortBy'])) {
            switch ($filters['sortBy']) {
                case 'date_asc':
                    $queryBuilder->orderBy('t.retiredate', 'ASC');
                    break;
                case 'product_name':
                    $queryBuilder->orderBy('cp.nameChimicalproduct', 'ASC');
                    break;
                case 'quantity':
                    $queryBuilder->orderBy('t.retirequantity', 'DESC');
                    break;
                case 'user_name':
                    $queryBuilder->orderBy('u.lastname', 'ASC')
                        ->addOrderBy('u.firstname', 'ASC');
                    break;
                default:
                    $queryBuilder->orderBy('t.retiredate', 'DESC');
                    break;
            }
        } else {
            // Tri par défaut
            $queryBuilder->orderBy('t.retiredate', 'DESC');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}