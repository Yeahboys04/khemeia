<?php

namespace App\Controller;

use App\Entity\Storagecard;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InventoryInfoController extends AbstractController
{
    #[Route('/inventory/stock', name: 'inventory_stock')]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        //try {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        $site = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();
        $storagecards = $repositoryStoragecard->loadStorageCardsBySite($site);
        $emptyStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndEmptyStock($site);
        $expirationStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndExpirationDate($site);
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/stock.html.twig', [
            'storagecards' => $storagecards,
            'emptyStoragecards' =>$emptyStoragecards,
            'expirationStoragecards' => $expirationStoragecards,
            'site' => $tokenStorage->getToken()->getUser()->getIdSite()
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

    #[Route('/inventory/alert', name: 'inventory_alert')]
    public function alertStock(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        //try {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        $site = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();
        $storagecards = $repositoryStoragecard->loadStorageCardsBySite($site);
        $emptyStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndEmptyStock($site);
        $expirationStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndExpirationDate($site);
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/alert.html.twig', [
            'storagecards' => $storagecards,
            'emptyStoragecards' =>$emptyStoragecards,
            'expirationStoragecards' => $expirationStoragecards,
            'site' => $tokenStorage->getToken()->getUser()->getIdSite()
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

    #[Route('/inventory/expiration', name: 'inventory_expiration')]
    public function expirationStock(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        //try {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        $site = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();
        $storagecards = $repositoryStoragecard->loadStorageCardsBySite($site);
        $emptyStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndEmptyStock($site);
        $expirationStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndExpirationDate($site);
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/expiration.html.twig', [
            'storagecards' => $storagecards,
            'emptyStoragecards' =>$emptyStoragecards,
            'expirationStoragecards' => $expirationStoragecards,
            'site' => $tokenStorage->getToken()->getUser()->getIdSite()
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

    #[Route('/inventory/site', name: 'inventory_make')]
    public function makeInventory(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        //try {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        $site = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();
        $storagecards = $repositoryStoragecard->loadStorageCardBySiteForPDF($site);
        $emptyStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndEmptyStock($site);
        $expirationStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndExpirationDate($site);
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/make.html.twig', [
            'storagecards' => $storagecards,
            'emptyStoragecards' =>$emptyStoragecards,
            'expirationStoragecards' => $expirationStoragecards,
            'site' => $tokenStorage->getToken()->getUser()->getIdSite()
        ]);

    }

    #[Route('inventory/export', name: 'inventory_export')]
    public function exportCSV(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $site = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();

        // Récupérer les données
        $results = $repositoryStoragecard->loadStorageCardBySiteForCSV($site)->iterate();

        // Créer un nom de fichier unique avec timestamp
        $filename = 'export_' . date('Y-m-d_H-i-s') . '.csv';
        $path = $this->getParameter('csv_directory') . "/" . $filename;

        if (file_exists($path)) {
            unlink($path);
        }

        $handle = fopen($path, 'x+');

        // Ajouter le BOM UTF-8 pour assurer la compatibilité avec les accents
        fputs($handle, "\xEF\xBB\xBF");


        // Écrire les données
        while (false !== ($rows = $results->next())) {
            foreach($rows as $row) {
                // S'assurer que chaque valeur est encodée correctement en UTF-8
                $encodedRow = array_map(function($value) {
                    if (is_string($value)) {
                        return mb_detect_encoding($value, 'UTF-8', true) ?
                            $value :
                            utf8_encode($value);
                    }
                    return $value;
                }, $row);

                fputcsv($handle, $encodedRow, ',', '"', '\\');
            }
            // Libérer la mémoire
            $entityManager->clear();
        }

        fclose($handle);

        // Configurer la réponse pour forcer le téléchargement
        $response = $this->file($path, $filename);
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }


}