<?php

namespace App\Service;

use App\Entity\Storagecard;
use App\Entity\Tracability;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer la traçabilité des produits
 */
class TracabilityService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Récupère l'historique de traçabilité d'un utilisateur
     */
    public function getUserTracability(int $userId): array
    {
        return $this->entityManager
            ->getRepository(Tracability::class)
            ->findTracabilityByUser($userId);
    }

    /**
     * Ajoute un produit à l'historique de traçabilité d'un utilisateur
     */
    public function addProductToTracability(Storagecard $storagecard, User $user): Tracability
    {
        $tracability = new Tracability();
        $tracability->setRetiredate(new DateTimeImmutable());
        $tracability->setRetirequantity(null);
        $tracability->setIdStoragecard($storagecard);
        $tracability->setIdUser($user);

        $this->entityManager->persist($tracability);
        $this->entityManager->flush();

        return $tracability;
    }
}