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
            //Initialise le repository pour la base de données
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            //Initialise le formulaire
            $storagecard = new Storagecard();

            $form = $this->createForm(SearchType::class, $storagecard, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            //Si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                //on récupère les données
                $storagecard = $form->get('idChimicalproduct')->getData();
                $site = $form->get('idSite')->getData();


                //Cherche tous les produits qui correspondent à la recherche
                $storagecards = $repositoryStoragecard->loadStorageCardBySite($site, $storagecard);

                //on retourne la page avec les informations trouvées
                return $this->render('search/index.html.twig', [
                    'form' => $form->createView(),
                    'storagecards' => $storagecards
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

        //Quoi qu'il arrive on rend la page initiale
        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}