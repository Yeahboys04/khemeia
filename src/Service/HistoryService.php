<?php

namespace App\Service;

use App\Entity\Movedhistory;
use App\Entity\Tracability;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer les historiques de déplacement et d'utilisation des produits
 */
class HistoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Récupère l'historique de déplacement d'une fiche de stockage
     */
    public function getMovedHistory(int $storagecardId): array
    {
        return $this->entityManager
            ->getRepository(Movedhistory::class)
            ->findAllByStoragecard($storagecardId);
    }

    /**
     * Récupère l'historique des utilisateurs ayant utilisé une fiche de stockage
     */
    public function getUserHistory(int $storagecardId): array
    {
        return $this->entityManager
            ->getRepository(Tracability::class)
            ->findAllByStoragecard($storagecardId);
    }
}