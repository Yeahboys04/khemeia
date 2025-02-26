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

        // La méthode getExportQuery retourne une Query qui est utilisée pour récupérer
        // tous les objets (lignes du fichier csv) dont vous avez besoin. La méthode iterate
        // est utilisée pour limiter la consommation de mémoire

        $results = $repositoryStoragecard->loadStorageCardBySiteForCSV($site)->iterate();
        //vérifier que le fichier existe déja
        $path = $this->getParameter('csv_directory')."/export.csv";
        if (file_exists($path)){
            //S'il existe déja, on le supprime / remplace?
            unlink($path);

        }
        $handle = fopen($path, 'x+');

        while (false !== ($rows = $results->next())) {
            foreach($rows as $row){
                fputcsv($handle, $row);
            }
            // ajoute une ligne au fichier csv. Vous devrez implémenter la méthode toArray()
            // pour transformer votre objet en tableau

            // utilisé pour limiter la consommation de mémoire
            //$entityManager->clear($row[0]);
        }

        fclose($handle);

        //$response->headers->set('Content-Type', 'application/force-download');
        //$response->headers->set('Content-Disposition','attachment; filename="export.csv"');

        return $this->file($path);
    }

    #[Route('inventory/pdf', name: 'inventory_pdf')]
    public function exportPDF(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        //try {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        $site = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();
        $storagecards = $repositoryStoragecard->loadStorageCardBySiteForPDF($site);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('pdf/pdf.html.twig', [
            'storagecards' => $storagecards,
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();

        $output = $dompdf->output();
        $pdfFilepath = $this->getParameter('pdf_directory')."/export.pdf";

        file_put_contents($pdfFilepath, $output);

        return $this->file($pdfFilepath);
    }
}