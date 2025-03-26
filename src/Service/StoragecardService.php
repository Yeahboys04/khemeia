<?php

namespace App\Service;

use App\Entity\IncompatibilityRequest;
use App\Entity\Movedhistory;
use App\Entity\Storagecard;
use App\Repository\StoragecardRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StoragecardService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Récupère toutes les fiches de stockage
     */
    public function getAllStoragecards(): array
    {
        return $this->entityManager->getRepository(Storagecard::class)->findAll();
    }

    /**
     * Récupère les données des fiches de stockage par site
     */
    public function getStoragecardDataBySite(int $siteId): array
    {
        $repository = $this->entityManager->getRepository(Storagecard::class);

        return [
            'regular' => $repository->loadStorageCardsBySite($siteId),
            'empty' => $repository->loadStorageCardsBySiteAndEmptyStock($siteId),
            'expiration' => $repository->loadStorageCardsBySiteAndExpirationDate($siteId),
        ];
    }

    /**
     * Récupère une fiche de stockage par son ID
     */
    public function getStoragecard(int $id): ?Storagecard
    {
        $repository = $this->entityManager->getRepository(Storagecard::class);
        return $repository->find($id);
    }

    /**
     * Prépare une copie d'une fiche de stockage existante
     */
    public function prepareStoragecardCopy(Storagecard $original): Storagecard
    {
        $copy = new Storagecard();

        // Copie des propriétés de base
        $copy->setStockquantity($original->getStockquantity());
        $copy->setCapacity($original->getCapacity());
        $copy->setPurity($original->getPurity());
        $copy->setSerialnumber($original->getSerialnumber());
        $copy->setTemperature($original->getTemperature());
        $copy->setOpendate($original->getOpendate());
        $copy->setExpirationdate($original->getExpirationdate());
        $copy->setIsarchived($original->getIsarchived());
        $copy->setIsrisked($original->getIsrisked());
        $copy->setIspublished($original->getIspublished());
        $copy->setIdChimicalproduct($original->getIdChimicalproduct());
        $copy->setIdShelvingunit($original->getIdShelvingunit());
        $copy->setIdProperty($original->getIdProperty());
        $copy->setIdSupplier($original->getIdSupplier());
        $copy->setReference($original->getReference());
        $copy->setStateType($original->getStateType());

        return $copy;
    }

    /**
     * Prépare une fiche de stockage dupliquée avec valeurs par défaut pour l'admin
     */
    public function prepareStoragecardDuplicate(Storagecard $original): Storagecard
    {
        $copy = $this->prepareStoragecardCopy($original);

        // Valeurs spécifiques pour une duplication
        $copy->setOpendate(new \DateTime());  // Date d'ouverture actuelle
        $copy->setIsarchived(false); // Par défaut non archivé

        // Ajouter un commentaire pour indiquer que c'est une copie
        if ($original->getCommentary()) {
            $copy->setCommentary($original->getCommentary() . ' (Copie)');
        } else {
            $copy->setCommentary('Copie de la fiche #' . $original->getIdStoragecard());
        }

        return $copy;
    }

    /**
     * Archive une fiche de stockage
     */
    public function archiveStoragecard(int $id): void
    {
        $storagecard = $this->getStoragecard($id);
        $storagecard->setIsarchived(true);

        $this->entityManager->persist($storagecard);
        $this->entityManager->flush();
    }

    /**
     * Supprime une fiche de stockage
     *
     * @throws ForeignKeyConstraintViolationException Si la fiche est référencée ailleurs
     */
    public function removeStoragecard(Storagecard $storagecard): void
    {
        $this->entityManager->remove($storagecard);
        $this->entityManager->flush();
    }

    /**
     * Crée une fiche de stockage à partir d'une demande d'incompatibilité
     */
    public function createStoragecardFromIncompatibilityRequest(int $productId, int $shelvingUnitId): Storagecard
    {
        $product = $this->entityManager->getRepository(\App\Entity\Chimicalproduct::class)->find($productId);
        $shelvingUnit = $this->entityManager->getRepository(\App\Entity\Shelvingunit::class)->find($shelvingUnitId);

        $storagecard = new Storagecard();
        $storagecard->setIdChimicalproduct($product);
        $storagecard->setIdShelvingunit($shelvingUnit);

        // Initialisation avec des valeurs par défaut
        $storagecard->setCapacity(0);
        $storagecard->setIsarchived(false);
        $storagecard->setIsrisked(false);
        $storagecard->setIspublished(false);

        return $storagecard;
    }

    /**
     * Sauvegarde une fiche de stockage et crée l'historique de mouvement si nécessaire
     */
    public function saveStoragecard(
        Storagecard $storagecard,
        string $operationType,
        bool $createMovedHistory = true
    ): Storagecard {
        // Définir la date de création pour les nouvelles fiches
        if ($operationType !== 'modify') {
            $storagecard->setCreationDate(new DateTimeImmutable());
        }

        $this->entityManager->persist($storagecard);
        $this->entityManager->flush();

        // Créer l'historique de mouvement si nécessaire
        if ($createMovedHistory) {
            $this->createMovedHistory($storagecard);
        }

        return $storagecard;
    }

    /**
     * Sauvegarde une fiche de stockage créée à partir d'une demande et marque la demande comme utilisée
     */
    public function saveStoragecardFromRequest(
        Storagecard $storagecard,
        IncompatibilityRequest $request
    ): Storagecard {
        $this->entityManager->persist($storagecard);

        // Créer l'historique du mouvement
        $this->createMovedHistory($storagecard);

        // Marquer la demande comme utilisée
        $request->setIsUsed(true);
        $this->entityManager->persist($request);

        $this->entityManager->flush();

        return $storagecard;
    }

    /**
     * Crée un enregistrement d'historique de mouvement
     */
    private function createMovedHistory(Storagecard $storagecard): void
    {
        $movedHistory = new Movedhistory();
        $movedHistory->setMovedate(new DateTimeImmutable());
        $movedHistory->setIdShelvingunit($storagecard->getIdShelvingunit());
        $movedHistory->setIdStoragecard($storagecard);
        $movedHistory->setIdUser($this->tokenStorage->getToken()->getUser());

        $this->entityManager->persist($movedHistory);
        $this->entityManager->flush();
    }
}