<?php

namespace App\Controller;

use App\Entity\Chimicalproduct;
use App\Entity\Site;
use App\Entity\Storagecard;
use App\Form\ExportFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExportController extends AbstractController
{
    #[Route('/inventory/export-filter', name: 'inventory_export_filter')]
    public function exportFilter(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        // Récupérer le site de l'utilisateur connecté
        $site = $tokenStorage->getToken()->getUser()->getIdSite();

        // Créer le formulaire de filtrage
        $formData = [
            'site' => $site,
            'defaultExport' => true,
            'allSites' => false,
        ];

        $form = $this->createForm(ExportFilterType::class, $formData, [
            'entityManager' => $entityManager,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            // Si l'utilisateur a choisi l'exportation par défaut pour GPUC
            if ($formData['defaultExport']) {
                return $this->redirectToRoute('inventory_export');
            }

            // Sinon, générer un CSV filtré
            return $this->generateFilteredCsv($formData, $entityManager, $request);
        }

        return $this->render('inventory/filter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function generateFilteredCsv(array $filterData, EntityManagerInterface $entityManager, Request $request): Response
    {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        // Récupérer les données filtrées
        $query = $this->createFilteredQuery($filterData, $repositoryStoragecard);
        $results = $query->iterate();

        // Créer le fichier CSV
        $filename = 'export_filtered_' . date('Y-m-d_H-i-s') . '.csv';
        $path = $this->getParameter('csv_directory') . "/" . $filename;

        if (file_exists($path)) {
            unlink($path);
        }

        $handle = fopen($path, 'x+');

        // Ajouter le BOM UTF-8 pour que Excel reconnaisse correctement l'encodage
        fputs($handle, "\xEF\xBB\xBF");

        // Ajouter les en-têtes CSV si demandé
        if ($filterData['includeHeaders']) {
            $headers = $this->getExportHeaders($filterData);
            fputcsv($handle, $headers, ',', '"', '\\');
        }

        // Remplir le fichier avec les données
        while (false !== ($rows = $results->next())) {
            foreach($rows as $row) {
                fputcsv($handle, $row);
            }
            $entityManager->clear();
        }

        fclose($handle);

        return $this->file($path, $filename);
    }

    private function createFilteredQuery(array $filterData, $repository, array $requestData = []): \Doctrine\ORM\Query
    {
        $qb = $repository->createQueryBuilder('sc');

        // Sélectionner les champs demandés
        $fields = [];

        if ($filterData['includeCasNumber']) {
            $fields[] = 'cp.casnumber';
        }

        if ($filterData['includeProductName']) {
            $fields[] = 'cp.nameChimicalproduct';
        }

        if ($filterData['includeQuantity']) {
            $fields[] = 'sc.stockquantity';
        }

        if ($filterData['includeCapacity']) {
            $fields[] = 'sc.capacity';
        }

        if ($filterData['includeLocation']) {
            $fields[] = 'st.nameStock';
            $fields[] = 'c.nameCupboard';
            $fields[] = 'sh.nameShelvingunit';
        }

        if ($filterData['includeSite']) {
            $fields[] = 's.nameSite';
        }

        if ($filterData['includeExpiration']) {
            $fields[] = 'sc.expirationdate';
        }

        if ($filterData['includeReference']) {
            $fields[] = 'sc.reference';
        }

        if ($filterData['includeSupplier']) {
            $fields[] = 'sup.nameSupplier';
        }

        if ($filterData['includePurity']) {
            $fields[] = 'sc.purity';
        }

        if ($filterData['includeOpenDate']) {
            $fields[] = 'sc.opendate';
        }

        if ($filterData['includeSerialNumber']) {
            $fields[] = 'sc.serialnumber';
        }

        // Si aucun champ n'est sélectionné, utiliser les champs par défaut pour GPUC
        if (empty($fields)) {
            $fields = ['cp.casnumber', 'cp.nameChimicalproduct', 'sc.stockquantity', 'c.nameCupboard', 'sh.nameShelvingunit', 's.nameSite'];
        }

        $qb->select($fields);

        // Jointures nécessaires
        $qb->innerJoin('App\Entity\Chimicalproduct', 'cp', 'WITH', 'sc.idChimicalproduct = cp.idChimicalproduct')
            ->innerJoin('App\Entity\Shelvingunit', 'sh', 'WITH', 'sc.idShelvingunit = sh.idShelvingunit')
            ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = sh.idCupboard')
            ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
            ->innerJoin('App\Entity\Site', 's', 'WITH', 'st.idSite = s.idSite');

        if ($filterData['includeSupplier']) {
            $qb->leftJoin('App\Entity\Supplier', 'sup', 'WITH', 'sc.idSupplier = sup.idSupplier');
        }

        // Appliquer les filtres
        $parameters = [];

        // Filtre sur le site
        if (!$filterData['allSites'] && $filterData['site']) {
            $qb->andWhere('st.idSite = :site');
            $parameters['site'] = $filterData['site'];
        }

        // Filtre sur l'état d'archivage
        if ($filterData['includeArchived'] === false) {
            $qb->andWhere('sc.isarchived = false');
        }

        // Filtre sur le niveau de stock
        if ($filterData['stockFilter'] === 'all') {
            // Pas de filtre supplémentaire
        } elseif ($filterData['stockFilter'] === 'normal') {
            $qb->andWhere('sc.stockquantity > (0.10*sc.capacity)');
        } elseif ($filterData['stockFilter'] === 'low') {
            $qb->andWhere('sc.stockquantity <= (0.10*sc.capacity) AND sc.stockquantity > 0');
        } elseif ($filterData['stockFilter'] === 'empty') {
            $qb->andWhere('sc.stockquantity = 0');
        }

        // Filtre sur l'expiration
        if ($filterData['expirationFilter'] === 'all') {
            // Pas de filtre supplémentaire
        } elseif ($filterData['expirationFilter'] === 'valid') {
            $qb->andWhere('sc.expirationdate >= :today OR sc.expirationdate IS NULL');
            $parameters['today'] = new \DateTime();
        } elseif ($filterData['expirationFilter'] === 'expired') {
            $qb->andWhere('sc.expirationdate < :today');
            $parameters['today'] = new \DateTime();
        }

        // Filtre sur le produit chimique
        if (!empty($filterData['product'])) {
            $qb->andWhere('sc.idChimicalproduct = :product');
            $parameters['product'] = $filterData['product'];
        }

        // Ajouter tous les paramètres
        if (!empty($parameters)) {
            $qb->setParameters($parameters);
        }

        return $qb->getQuery();
    }

    private function getExportHeaders(array $filterData): array
    {
        $headers = [];

        if ($filterData['includeCasNumber']) {
            $headers[] = 'Numéro CAS';
        }

        if ($filterData['includeProductName']) {
            $headers[] = 'Nom du produit';
        }

        if ($filterData['includeQuantity']) {
            $headers[] = 'Quantité en stock';
        }

        if ($filterData['includeCapacity']) {
            $headers[] = 'Capacité';
        }

        if ($filterData['includeLocation']) {
            $headers[] = 'Stock';
            $headers[] = 'Armoire';
            $headers[] = 'Étagère';
        }

        if ($filterData['includeSite']) {
            $headers[] = 'Site';
        }

        if ($filterData['includeExpiration']) {
            $headers[] = 'Date d\'expiration';
        }

        if ($filterData['includeReference']) {
            $headers[] = 'Référence';
        }

        if ($filterData['includeSupplier']) {
            $headers[] = 'Fournisseur';
        }

        if ($filterData['includePurity']) {
            $headers[] = 'Pureté';
        }

        if ($filterData['includeOpenDate']) {
            $headers[] = 'Date d\'ouverture';
        }

        if ($filterData['includeSerialNumber']) {
            $headers[] = 'Numéro de série';
        }

        return $headers;
    }
}