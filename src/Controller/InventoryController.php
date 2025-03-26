<?php

namespace App\Controller;

use App\Entity\IncompatibilityRequest;
use App\Entity\Storagecard;
use DateTimeImmutable;
use Exception;
use LogicException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InventoryController extends AbstractStorageController
{
    #[Route('/inventory', name: 'inventory')]
    public function index(): Response
    {
        try {
            $user = $this->getUser();
            $siteId = $user->getIdSite()->getIdSite();

            $storagecardData = $this->storagecardService->getStoragecardDataBySite($siteId);

            return $this->render('inventory/index.html.twig', [
                'storagecards' => $storagecardData['regular'],
                'emptyStoragecards' => $storagecardData['empty'],
                'expirationStoragecards' => $storagecardData['expiration'],
                'site' => $user->getIdSite()
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/inventory/product', name: 'inventory_product')]
    public function createProduct(Request $request): Response
    {
        try {
            $product = new \App\Entity\Chimicalproduct();
            $form = $this->createForm(\App\Form\ChimicalproductType::class, $product, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $product = $this->inventoryService->createProduct($form->getData());

                $this->addFlash('success',
                    'Le produit numéro ' . $product->getIdChimicalproduct() . ' a été créé avec succès.');

                return $this->redirectToRoute('inventory_product');
            }

            return $this->render('inventory/product.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/api/search/products', name: 'api_search_products', methods: ['GET'])]
    public function searchProducts(Request $request): Response
    {
        $term = $request->query->get('q', '');

        if (empty($term) || strlen($term) < 2) {
            return new Response('[]', Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        $results = $this->inventoryService->searchProducts($term);

        return new Response(json_encode($results), Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/inventory/storagecard', name: 'inventory_storage')]
    public function createStoragecard(Request $request): Response
    {
        try {
            $storagecard = new Storagecard();

            $form = $this->createStoragecardForm($storagecard, 'create');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Définir la date de création
                $storagecard->setCreationDate(new DateTimeImmutable());

                $result = $this->processStoragecardForm(
                    $request,
                    $form,
                    $storagecard,
                    'create'
                );

                if ($result instanceof Response) {
                    return $result;
                }

                return $this->redirectToRoute('inventory_storage');
            }

            return $this->renderStorageForm($form, 'create');
        } catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->renderStorageForm($form, 'create', true);
        } catch (FileException $e) {
            $this->addFlash('error', 'Une erreur est survenue lors du déplacement du fichier. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        } catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/inventory/storagecard/{id}', name: 'inventory_storage_copy')]
    public function copyStoragecard(int $id, Request $request): Response
    {
        try {
            $originalStoragecard = $this->storagecardService->getStoragecard($id);

            if (!$originalStoragecard) {
                $this->addFlash('error', 'La fiche de stockage demandée n\'existe pas.');
                return $this->redirectToRoute('inventory');
            }

            $newStoragecard = $this->storagecardService->prepareStoragecardCopy($originalStoragecard);

            $form = $this->createStoragecardForm($newStoragecard, 'copy');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Définir la date de création
                $newStoragecard->setCreationDate(new DateTimeImmutable());

                $oldSecurityFile = $originalStoragecard->getIdSecurityfile();
                $oldAnalysisFile = $originalStoragecard->getIdAnalysisfile();

                $result = $this->processStoragecardForm(
                    $request,
                    $form,
                    $newStoragecard,
                    'copy',
                    true,
                    $oldSecurityFile,
                    $oldAnalysisFile
                );

                if ($result instanceof Response) {
                    return $result;
                }

                return $this->redirectToRoute('inventory_storage');
            }

            return $this->renderStorageForm($form, 'copy');
        } catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->renderStorageForm($form, 'copy', true);
        } catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/inventory/archived/{id}', name: 'inventory_archived')]
    public function archivedStoragecard(int $id): Response
    {
        try {
            $this->storagecardService->archiveStoragecard($id);
            $this->addFlash('success', 'La fiche de stockage a été archivée avec succès.');

            return $this->redirectToRoute('inventory');
        } catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/inventory/modify/{id}', name: 'inventory_modify')]
    public function modifyStoragecard(int $id, Request $request): Response
    {
        try {
            $storagecard = $this->storagecardService->getStoragecard($id);

            if (!$storagecard) {
                $this->addFlash('error', 'La fiche de stockage demandée n\'existe pas.');
                return $this->redirectToRoute('inventory');
            }

            $oldSecurityFile = $storagecard->getIdSecurityfile();
            $oldAnalysisFile = $storagecard->getIdAnalysisfile();
            $oldShelvingUnitId = $storagecard->getIdShelvingunit()->getIdShelvingunit();

            $form = $this->createStoragecardForm($storagecard, 'modify');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Vérifier si le produit a été déplacé
                $newShelvingUnit = $form->get('idShelvingunit')->getData();
                $hasMoved = $oldShelvingUnitId != $newShelvingUnit->getIdShelvingunit();

                $result = $this->processStoragecardForm(
                    $request,
                    $form,
                    $storagecard,
                    'modify',
                    $hasMoved,
                    $oldSecurityFile,
                    $oldAnalysisFile
                );

                if ($result instanceof Response) {
                    return $result;
                }

                return $this->redirectToRoute('inventory_make');
            }

            return $this->renderStorageForm($form, 'modify');
        } catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->renderStorageForm($form, 'modify', true);
        } catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/inventory/storagecard/from-request/{productId}/{shelvingUnitId}', name: 'inventory_storage_from_request')]
    public function createStoragecardFromRequest(
        Request $request,
        int $productId,
        int $shelvingUnitId
    ): Response
    {
        try {
            $requestId = $request->getSession()->get('incompatibility_request_id');

            if (!$requestId) {
                $this->addFlash('error', 'Impossible de retrouver la demande de dérogation. Veuillez réessayer.');
                return $this->redirectToRoute('user_incompatibility_requests');
            }

            $incompatibilityRequest = $this->validateIncompatibilityRequest($requestId, $productId, $shelvingUnitId);

            if ($incompatibilityRequest instanceof Response) {
                return $incompatibilityRequest;
            }

            $storagecard = $this->storagecardService->createStoragecardFromIncompatibilityRequest(
                $productId,
                $shelvingUnitId
            );

            $form = $this->createStoragecardForm($storagecard, 'create', true);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $storagecard = $form->getData();
                $storagecard->setCreationDate(new DateTimeImmutable());

                // Traiter les fichiers
                $storagecard = $this->handleFileUploads($form, $storagecard);

                // Sauvegarder et créer l'historique
                $this->storagecardService->saveStoragecardFromRequest($storagecard, $incompatibilityRequest);

                // Supprimer l'ID de la session
                $request->getSession()->remove('incompatibility_request_id');

                $this->addFlash('success',
                    'La fiche de stockage numéro ' . $storagecard->getIdStoragecard()
                    . ' a été créée avec succès à partir de votre demande de dérogation.');

                return $this->redirectToRoute('inventory');
            }

            $this->addFlash('info',
                'Vous êtes en train de créer une fiche de stockage à partir d\'une demande de dérogation approuvée. ' .
                'Le produit et l\'emplacement ne peuvent pas être modifiés.');

            return $this->renderStorageForm(
                $form,
                'create',
                false,
                [
                    'from_derogation' => true,
                    'product' => $storagecard->getIdChimicalproduct(),
                    'shelvingUnit' => $storagecard->getIdShelvingunit()
                ]
            );
        } catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    /**
     * Implémentation de la méthode abstraite pour le rendu des formulaires
     */
    protected function renderStorageForm(
        $form,
        string $operationType,
        bool $incompatibilityDetected = false,
        array $additionalParams = []
    ): Response {
        $params = [
            'form' => $form->createView(),
            'operation_type' => $operationType,
            'show_override' => $this->isGranted('ROLE_ADMIN'),
            'incompatibility_detected' => $incompatibilityDetected,
        ];

        // Ajouter les paramètres additionnels
        foreach ($additionalParams as $key => $value) {
            $params[$key] = $value;
        }

        return $this->render('inventory/storagecard.html.twig', $params);
    }

    /**
     * Valide une demande d'incompatibilité
     */
    private function validateIncompatibilityRequest(
        int $requestId,
        int $productId,
        int $shelvingUnitId
    ): IncompatibilityRequest|Response {
        $incompatibilityRequest = $this->inventoryService->findIncompatibilityRequest($requestId);

        if (!$incompatibilityRequest ||
            $incompatibilityRequest->getStatus() !== 'approved' ||
            $incompatibilityRequest->getIsUsed() === true) {
            $this->addFlash('error', 'Cette demande de dérogation n\'est plus valide ou a déjà été utilisée.');
            return $this->redirectToRoute('user_incompatibility_requests');
        }

        $product = $this->inventoryService->findProduct($productId);
        $shelvingUnit = $this->inventoryService->findShelvingUnit($shelvingUnitId);

        if (!$product || !$shelvingUnit) {
            $this->addFlash('error', 'Produit ou emplacement non trouvé.');
            return $this->redirectToRoute('user_incompatibility_requests');
        }

        if ($incompatibilityRequest->getProduct()->getIdChimicalproduct() != $productId ||
            $incompatibilityRequest->getShelvingUnit()->getIdShelvingunit() != $shelvingUnitId) {
            $this->addFlash('error', 'Les informations ne correspondent pas à la demande de dérogation originale.');
            return $this->redirectToRoute('user_incompatibility_requests');
        }

        return $incompatibilityRequest;
    }
}

