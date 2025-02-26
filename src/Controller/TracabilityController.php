<?php

namespace App\Controller;

use App\Entity\Storagecard;
use App\Entity\Tracability;
use App\Form\SearchType;
use App\Form\TracabilityType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TracabilityController extends AbstractController
{
    #[Route('/tracability', name: 'tracability')]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        //try {
        //Initialise le repository pour la base de données
        $repositoryTracability = $entityManager->getRepository(Tracability::class);

        $user = $tokenStorage->getToken()->getUser();

        $tracability = $repositoryTracability->findTracabilityByUser($user->getIdUser());

        return $this->render('tracability/index.html.twig', [
            'tracability' => $tracability,
        ]);
        //}
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     //on redirige vers la page d'accueil
        //     return $this->redirectToRoute('home_page');
        // }
    }

    #[Route('/tracability/cart/{id}', name: 'tracability_add')]
    public function addProduct(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        //try {
        $user = $tokenStorage->getToken()->getUser();

        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $storagecard = $repositoryStoragecard->find($id);

        $tracability = new Tracability();
        $tracability->setRetiredate(new DateTime());
        $tracability->setRetirequantity(null);
        $tracability->setIdStoragecard($storagecard);
        $tracability->setIdUser($user);
        $entityManager->persist($tracability);
        $entityManager->flush();

        $this->addFlash('success',
            'Le produit a bien été ajouté à votre historique.');
        return $this->redirectToRoute('tracability_search');
        //}
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     //on redirige vers la page d'accueil
        //     return $this->redirectToRoute('home_page');
        // }
    }

    #[Route('/tracability/search', name: 'tracability_search')]
    public function search(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        //try {
        //Initialise le repository pour la base de données
        $user = $tokenStorage->getToken()->getUser();

        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $storagecard = new Storagecard();

        $formStoragecard = $this->createForm(SearchType::class, $storagecard, [
            'method' => 'POST',
        ]);
        $formStoragecard->handleRequest($request);
        if ($formStoragecard->isSubmitted() && $formStoragecard->isValid()) {
            $storagecard = $formStoragecard->get('idChimicalproduct')->getData();
            $site = $formStoragecard->get('idSite')->getData();


            //Cherche tous les produits qui correspondent à la recherche
            $storagecards = $repositoryStoragecard->loadStorageCardBySite($site, $storagecard);

            //on retourne la page avec les informations trouvées
            return $this->render('tracability/cart.html.twig', [
                'formStoragecard' => $formStoragecard->createView(),
                'storagecards' => $storagecards
            ]);
        }
        //}
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     //on redirige vers la page d'accueil
        //     return $this->redirectToRoute('home_page');
        // }
        return $this->render('tracability/cart.html.twig', [
            'formStoragecard' => $formStoragecard->createView(),
        ]);
    }
}