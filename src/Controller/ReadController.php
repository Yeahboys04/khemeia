<?php

namespace App\Controller;

use App\Entity\Storagecard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReadController extends AbstractController
{
    #[Route('/read/{id}', name: 'read')]
    public function index(EntityManagerInterface $entityManager, $id): Response
    {
        //try {
        //Initialise le repository pour la base de données
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        //Cherche la fiche produit
        $storagecard = $repositoryStoragecard->find($id);

        return $this->render('read/index.html.twig', [
            'id' => $id,
            'storagecard' => $storagecard
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
}