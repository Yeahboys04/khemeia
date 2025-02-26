<?php

namespace App\Controller;

use App\Entity\Tracability;
use App\Entity\Storagecard;
use App\Entity\Movedhistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MovedHistoryController extends AbstractController
{
    #[Route('/history/{id}', name: 'moved_history')]
    public function movedHistory(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        //try {
        //Initialise le repository pour la base de données
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $repositoryMovedHistory = $entityManager->getRepository(Movedhistory::class);

        //Cherche la fiche de stockage
        $storageCard = $repositoryStoragecard->find($id);
        $movedHistory = $repositoryMovedHistory->findAllByStoragecard($storageCard->getIdStoragecard());

        return $this->render('history/movedproduct.html.twig', [
            'id' => $id,
            'storageCard' => $storageCard,
            'movedHistory' => $movedHistory
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
        return $this->render('history/movedproduct.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/history/user/{id}', name: 'tracability_history')]
    public function userHistory(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        //try {
        //Initialise le repository pour la base de données
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $repositoryTracability = $entityManager->getRepository(Tracability::class);

        //Cherche la fiche de stockage
        $storageCard = $repositoryStoragecard->find($id);
        $tracability = $repositoryTracability->findAllByStoragecard($storageCard->getIdStoragecard());

        return $this->render('history/productuser.html.twig', [
            'id' => $id,
            'storageCard' => $storageCard,
            'tracability' => $tracability
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
        return $this->render('history/movedproduct.html.twig', [
            'id' => $id,
        ]);
    }
}