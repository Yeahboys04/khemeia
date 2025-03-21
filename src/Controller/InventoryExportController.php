<?php

namespace App\Controller;

use App\Entity\Storagecard;
use App\Form\InventoryExportFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InventoryExportController extends AbstractController
{
    #[Route('/inventory/pdf', name: 'inventory_pdf')]
    public function exportFilterForm(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        $form = $this->createForm(InventoryExportFilterType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            // Store filter data in session for the actual export
            $request->getSession()->set('inventory_export_filters', $formData);

            // Redirect to the actual export action
            return $this->redirectToRoute('inventory_export_pdf');
        }

        // Vérifier si l'utilisateur est administrateur
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        return $this->render('inventory/export_filter.html.twig', [
            'form' => $form->createView(),
            'site' => $tokenStorage->getToken()->getUser()->getIdSite(),
            'is_admin' => $isAdmin
        ]);
    }

    #[Route('/inventory/export_pdf', name: 'inventory_export_pdf')]
    public function exportFilteredPDF(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        // Increase memory limit for PDF generation
        ini_set('memory_limit', '512M');

        // Get filter data from session
        $filters = $request->getSession()->get('inventory_export_filters', []);
        $defaultFilter = $filters['defaultFilter'] ?? true;

        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        // Déterminer si on doit afficher tous les sites (admin uniquement)
        $allSites = false;
        if ($this->isGranted('ROLE_ADMIN') && isset($filters['allSites']) && $filters['allSites']) {
            $allSites = true;
        }

        // Déterminer le site selon le filtre ou utiliser celui de l'utilisateur si pas "tous les sites"
        $selectedSite = null;
        if (!$allSites) {
            if ($this->isGranted('ROLE_ADMIN') && isset($filters['site']) && $filters['site']) {
                // Si admin et un site a été sélectionné, utiliser ce site
                $selectedSite = $filters['site']->getIdSite();
            } else {
                // Sinon utiliser le site de l'utilisateur connecté
                $selectedSite = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();
            }
        }

        // Récupérer l'entité Site pour l'affichage dans le PDF
        $siteEntity = $tokenStorage->getToken()->getUser()->getIdSite();
        if ($this->isGranted('ROLE_ADMIN')) {
            if ($allSites) {
                $siteEntity = "Tous les sites";
            } elseif (isset($filters['site']) && $filters['site']) {
                $siteEntity = $filters['site'];
            }
        }

        // Récupérer les cartes de stockage selon les filtres
        if ($defaultFilter) {
            // Si filtre par défaut et site spécifique
            if (!$allSites) {
                $storagecards = $repositoryStoragecard->loadStorageCardBySiteForPDF($selectedSite);
            } else {
                // Si admin veut tous les sites, on doit modifier notre approche
                // Méthode pour charger tous les sites (à créer si elle n'existe pas)
                // Ici nous utilisons une approche simplifiée - charger tous les produits non archivés
                $storagecards = $entityManager->createQueryBuilder()
                    ->select('sc')
                    ->from('App\Entity\Storagecard', 'sc')
                    ->where('sc.isarchived = false')
                    ->getQuery()
                    ->getResult();
            }
        } else {
            // Si filtres personnalisés
            $stockStatus = $filters['stockStatus'] ?? 'all';
            $filterByCMR = $filters['filterByCMR'] ?? false;
            $product = $filters['product'] ?? null;
            $location = $filters['location'] ?? null;

            // Si on veut tous les sites (admin uniquement)
            if ($allSites) {
                // Requête générique selon le statut du stock
                $qb = $entityManager->createQueryBuilder()
                    ->select('sc')
                    ->from('App\Entity\Storagecard', 'sc')
                    ->where('sc.isarchived = false');

                // Ajouter des conditions selon le statut du stock
                if ($stockStatus === 'sufficient') {
                    $qb->andWhere('(sc.stockquantity > (0.10*sc.capacity)) OR sc.stockquantity IS NULL')
                        ->andWhere('sc.expirationdate >= :querydate OR sc.expirationdate IS NULL')
                        ->setParameter('querydate', new \DateTime());
                } elseif ($stockStatus === 'low_expired') {
                    $qb->andWhere('(sc.stockquantity <= (0.10*sc.capacity) AND sc.stockquantity != 0) OR sc.expirationdate < :querydate')
                        ->setParameter('querydate', new \DateTime());
                } elseif ($stockStatus === 'empty') {
                    $qb->andWhere('sc.stockquantity = 0');
                }

                $storagecards = $qb->getQuery()->getResult();
            } else {
                // Filtres pour un site spécifique
                if ($stockStatus === 'sufficient') {
                    $storagecards = $repositoryStoragecard->loadStorageCardsBySite($selectedSite);
                } elseif ($stockStatus === 'low_expired') {
                    $storagecards = $repositoryStoragecard->loadStorageCardsBySiteAndExpirationDate($selectedSite);
                } elseif ($stockStatus === 'empty') {
                    $storagecards = $repositoryStoragecard->loadStorageCardsBySiteAndEmptyStock($selectedSite);
                } else {
                    // Default to all products for the site
                    $storagecards = $repositoryStoragecard->loadStorageCardBySiteForPDF($selectedSite);
                }
            }

            // Filtrage supplémentaire en PHP
            if ($filterByCMR || $product || $location) {
                $storagecards = array_filter($storagecards, function($card) use ($filterByCMR, $product, $location) {
                    // Check CMR filter
                    if ($filterByCMR && (!$card->getIdChimicalproduct() || !$card->getIdChimicalproduct()->getIscmr())) {
                        return false;
                    }

                    // Check product filter
                    if ($product && $card->getIdChimicalproduct() && $card->getIdChimicalproduct()->getIdChimicalproduct() !== $product->getIdChimicalproduct()) {
                        return false;
                    }

                    // Check location filter (shelving unit)
                    if ($location && $card->getIdShelvingunit() && $card->getIdShelvingunit()->getIdShelvingunit() !== $location->getIdShelvingunit()) {
                        return false;
                    }

                    return true;
                });
            }
        }

        // Check if we have too many records - paginate if necessary
        $itemsPerPage = 50000; // Adjust this number based on complexity of the records
        $totalItems = count($storagecards);

        // If too many items, split into multiple PDFs or limit the export
        if ($totalItems > $itemsPerPage) {
            // Only take first batch (safer for memory)
            $storagecards = array_slice($storagecards, 0, $itemsPerPage);

            // Add a flash message to inform user about the limitation
            $this->addFlash(
                'warning',
                "L'export a été limité aux $itemsPerPage premiers produits pour éviter des problèmes de mémoire. Utilisez des filtres plus précis pour réduire le nombre d'éléments."
            );
        }

        // Generate PDF with memory optimization
        return $this->generateOptimizedPdf($storagecards, $filters, $siteEntity);
    }

    /**
     * Generate optimized PDF with memory-efficient settings
     */
    private function generateOptimizedPdf(
        array $storagecards,
        array $filters,
              $siteEntity
    ): Response
    {
        // Configure DomPDF with memory optimization
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', false); // Disable remote images
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isPhpEnabled', false); // Disable PHP code in templates
        $pdfOptions->set('isFontSubsettingEnabled', true); // Enable font subsetting

        // Create Dompdf instance with options
        $dompdf = new Dompdf($pdfOptions);

        // Prepare data for template - be careful about what you pass
        $templateData = [
            'storagecards' => $storagecards,
            'filters' => $filters,
            'site' => $siteEntity,
            'showDetails' => $filters['showDetails'] ?? false,
            'showLocation' => $filters['showLocation'] ?? true,
            'showQuantity' => $filters['showQuantity'] ?? true,
            'showOpenDate' => $filters['showOpenDate'] ?? false, // Ajout de la variable showOpenDate
            'showExpiration' => $filters['showExpiration'] ?? true,
            'showCMR' => $filters['showCMR'] ?? false,
            'showSupplier' => $filters['showSupplier'] ?? false,
            'showSymbols' => $filters['showSymbols'] ?? false,
        ];

        // Render HTML template
        $html = $this->renderView('pdf/filtered_pdf.html.twig', $templateData);

        // Load HTML and render PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');

        // Clear some memory before rendering
        unset($html);
        unset($templateData);
        unset($storagecards);

        // Force PHP garbage collection
        gc_collect_cycles();

        // Render the PDF
        $dompdf->render();

        // Stream PDF directly to browser instead of writing to file
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="inventaire_' . date('Y-m-d') . '.pdf"'
            ]
        );
    }
}