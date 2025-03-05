<?php

namespace App\Controller;

use App\Entity\Storagecard;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            // Initialise le repository pour la base de données
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            // Initialise le formulaire
            $storagecard = new Storagecard();

            $form = $this->createForm(SearchType::class, $storagecard, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);

            // Si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupère le type de recherche (produit ou CAS)
                $searchType = $form->get('searchType')->getData();

                // Récupère l'option de recherche sur tous les sites
                $searchAll = $form->get('searchAll')->getData();

                // Récupère le site sélectionné seulement si on ne cherche pas sur tous les sites
                $site = null;
                if ($searchAll === 'non') {
                    $site = $form->get('idSite')->getData();
                }

                // Récupère le produit chimique selon le type de recherche
                if ($searchType === 'cas') {
                    // Recherche par numéro CAS
                    $chimicalProduct = $form->get('casSearch')->getData();
                } else {
                    // Recherche par nom de produit (par défaut)
                    $chimicalProduct = $form->get('idChimicalproduct')->getData();
                }

                // Si aucun produit n'est sélectionné, affiche un message d'erreur
                if (!$chimicalProduct) {
                    $this->addFlash('error', 'Veuillez sélectionner un produit.');
                    return $this->render('search/index.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // Recherche les produits correspondants (selon option tous sites ou site spécifique)
                if ($searchAll === 'oui') {
                    // Fonction à créer dans le repository ou utilisation de findBy
                    $storagecards = $repositoryStoragecard->loadStorageCardsByCAS($chimicalProduct->getCasnumber());
                } else {
                    $storagecards = $repositoryStoragecard->loadStorageCardBySite($site, $chimicalProduct);
                }

                // Rend la page avec les résultats
                return $this->render('search/index.html.twig', [
                    'form' => $form->createView(),
                    'storagecards' => $storagecards,
                    'site' => $site
                ]);
            }
        }
            // S'il y a toute autre exception
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }

        // Quoi qu'il arrive on rend la page initiale
        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}