<?php

namespace App\Service;

use App\Entity\Chimicalproduct;
use App\Entity\IncompatibilityRequest;
use App\Entity\Shelvingunit;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class InventoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Utility $utility
    ) {
    }

    /**
     * Crée un nouveau produit chimique
     */
    public function createProduct(Chimicalproduct $product): Chimicalproduct
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    /**
     * Recherche des produits par terme
     */
    public function searchProducts(string $term): array
    {
        $repository = $this->entityManager->getRepository(Chimicalproduct::class);
        $products = $repository->createQueryBuilder('p')
            ->where('p.nameChimicalproduct LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.nameChimicalproduct', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->getIdChimicalproduct(),
                'text' => $product->getNameChimicalproduct(),
                'formula' => $product->getFormula(),
                'casnumber' => $product->getCasnumber(),
                'exists' => true
            ];
        }

        return $results;
    }

    /**
     * Vérifie la compatibilité entre un emplacement et un produit chimique
     */
    public function checkCompatibility(
        Shelvingunit $shelvingunit,
        Chimicalproduct $product,
        bool $overrideCheck = false
    ): bool {
        return $this->utility->movedIsAuthorised($shelvingunit, $product, $this->entityManager, $overrideCheck);
    }

    /**
     * Trouve une demande d'incompatibilité par ID
     */
    public function findIncompatibilityRequest(int $id): ?IncompatibilityRequest
    {
        return $this->entityManager->getRepository(IncompatibilityRequest::class)->find($id);
    }

    /**
     * Trouve un produit chimique par ID
     */
    public function findProduct(int $id): ?Chimicalproduct
    {
        return $this->entityManager->getRepository(Chimicalproduct::class)->find($id);
    }

    /**
     * Trouve une unité de rangement par ID
     */
    public function findShelvingUnit(int $id): ?Shelvingunit
    {
        return $this->entityManager->getRepository(Shelvingunit::class)->find($id);
    }
}