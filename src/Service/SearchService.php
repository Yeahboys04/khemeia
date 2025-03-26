<?php

namespace App\Service;

use App\Entity\Chimicalproduct;
use App\Entity\Site;
use App\Entity\Storagecard;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Service centralisé pour la gestion des recherches de produits
 */
class SearchService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory
    ) {
    }

    /**
     * Crée un formulaire de recherche
     */
    public function createSearchForm(array $options = []): FormInterface
    {
        $storagecard = new Storagecard();
        $defaultOptions = [
            'method' => 'POST',
        ];

        $options = array_merge($defaultOptions, $options);

        return $this->formFactory->create(SearchType::class, $storagecard, $options);
    }

    /**
     * Traite un formulaire de recherche et retourne les résultats
     *
     * @return array Résultats de la recherche et paramètres
     */
    public function processSearchForm(FormInterface $form, Request $request): array
    {
        $results = [
            'storagecards' => null,
            'site' => null,
            'searchAll' => null,
            'error' => null,
            'success' => true
        ];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le type de recherche (produit ou CAS)
            $searchType = $form->get('searchType')->getData();

            // Récupère l'option de recherche sur tous les sites
            $searchAll = $form->get('searchAll')->getData();
            $results['searchAll'] = $searchAll;

            // Récupère le site sélectionné seulement si on ne cherche pas sur tous les sites
            $site = null;
            if ($searchAll === 'non') {
                $site = $form->get('idSite')->getData();
            }
            $results['site'] = $site;

            // Récupère le produit chimique selon le type de recherche
            $chimicalProduct = $this->getChimicalProductFromForm($form, $searchType);

            if (!$chimicalProduct) {
                $results['error'] = 'Veuillez sélectionner un produit.';
                return $results;
            }

            // Recherche les produits correspondants
            $storagecards = $this->searchStoragecards($searchAll, $site, $chimicalProduct);
            $results['storagecards'] = $storagecards;
        }

        return $results;
    }

    /**
     * Récupère le produit chimique du formulaire selon le type de recherche
     */
    private function getChimicalProductFromForm(FormInterface $form, string $searchType): ?Chimicalproduct
    {
        if ($searchType === 'cas') {
            // Recherche par numéro CAS
            return $form->get('casSearch')->getData();
        } else {
            // Recherche par nom de produit (par défaut)
            return $form->get('idChimicalproduct')->getData();
        }
    }

    /**
     * Recherche les fiches de stockage selon les critères
     */
    private function searchStoragecards(string $searchAll, ?Site $site, Chimicalproduct $chimicalProduct): array
    {
        $repositoryStoragecard = $this->entityManager->getRepository(Storagecard::class);

        if ($searchAll === 'oui') {
            // Recherche sur tous les sites par numéro CAS
            return $repositoryStoragecard->loadStorageCardsByCAS($chimicalProduct->getCasnumber());
        } else {
            // Recherche sur un site spécifique
            return $repositoryStoragecard->loadStorageCardBySite($site, $chimicalProduct);
        }
    }

    /**
     * Recherche une fiche de stockage par ID
     */
    public function findStorageCard(int $id): ?Storagecard
    {
        return $this->entityManager->getRepository(Storagecard::class)->find($id);
    }
}