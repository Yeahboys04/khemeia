<?php

namespace App\Controller;

use App\Entity\Storagecard;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Exception;
use LogicException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminStorageController extends AbstractStorageController
{
    #[Route('/admin/storage', name: 'admin_storage')]
    public function index(Request $request): Response
    {
        try {
            $storagecards = $this->storagecardService->getAllStoragecards();
            $storagecard = new Storagecard();

            $form = $this->createStoragecardForm($storagecard, 'create');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $storagecard = $form->getData();

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

                return $this->redirectToRoute('admin_storage');
            }

            return $this->renderStorageForm($form, 'create', false, ['storagecards' => $storagecards]);
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->renderStorageForm($form, 'create', true, ['storagecards' => $storagecards]);
        }
        catch (FileException $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue lors du déplacement du fichier. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
        catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/admin/storage/modify/{id}', name: 'admin_storage_modify')]
    public function modify(Request $request, int $id): Response
    {
        try {
            $storagecards = $this->storagecardService->getAllStoragecards();
            $storagecard = $this->storagecardService->getStoragecard($id);

            if (!$storagecard) {
                $this->addFlash('error', 'La fiche de stockage N° ' . $id . ' n\'existe pas.');
                return $this->redirectToRoute('admin_storage');
            }

            $previousLocalisation = $storagecard->getIdShelvingunit();
            $oldSecurityFile = $storagecard->getIdSecurityfile();
            $oldAnalysisFile = $storagecard->getIdAnalysisfile();

            $form = $this->createStoragecardForm($storagecard, 'modify');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Vérifier si l'emplacement a changé
                $newLocalisation = $form->get('idShelvingunit')->getData();
                $hasMoved = $newLocalisation !== $previousLocalisation;

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

                return $this->redirectToRoute('admin_storage');
            }

            return $this->renderStorageForm(
                $form,
                'modify',
                false,
                ['storagecards' => $storagecards, 'id' => $id]
            );
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->renderStorageForm(
                $form,
                'modify',
                true,
                ['storagecards' => $storagecards, 'id' => $id]
            );
        }
        catch (FileException $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue lors du déplacement du fichier. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
        catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/admin/storage/duplicate/{id}', name: 'admin_storage_duplicate')]
    public function duplicate(Request $request, int $id): Response
    {
        try {
            $storagecards = $this->storagecardService->getAllStoragecards();
            $originalStoragecard = $this->storagecardService->getStoragecard($id);

            if (!$originalStoragecard) {
                $this->addFlash('error', 'La fiche de stockage N° ' . $id . ' n\'existe pas.');
                return $this->redirectToRoute('admin_storage');
            }

            // Préparer une copie de la fiche d'origine avec les spécificités de duplication
            $newStoragecard = $this->storagecardService->prepareStoragecardDuplicate($originalStoragecard);
            $form = $this->createStoragecardForm($newStoragecard, 'duplicate');

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $oldSecurityFile = $originalStoragecard->getIdSecurityfile();
                $oldAnalysisFile = $originalStoragecard->getIdAnalysisfile();

                $result = $this->processStoragecardForm(
                    $request,
                    $form,
                    $newStoragecard,
                    'duplicate',
                    true,
                    $oldSecurityFile,
                    $oldAnalysisFile
                );

                if ($result instanceof Response) {
                    return $result;
                }

                return $this->redirectToRoute('admin_storage');
            }

            return $this->renderStorageForm(
                $form,
                'duplicate',
                false,
                ['storagecards' => $storagecards, 'id' => $id]
            );
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->renderStorageForm(
                $form,
                'duplicate',
                true,
                ['storagecards' => $storagecards, 'id' => $id]
            );
        }
        catch (FileException $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue lors du déplacement du fichier. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
        catch (Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue. Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
    }

    #[Route('/admin/storage/remove/{id}', name: 'admin_storage_remove')]
    public function remove(Request $request, int $id): Response
    {
        try {
            $storagecards = $this->storagecardService->getAllStoragecards();
            $storagecard = $this->storagecardService->getStoragecard($id);

            if (!$storagecard) {
                $this->addFlash('error', 'La fiche de stockage N° ' . $id . ' n\'existe pas.');
                return $this->redirectToRoute('admin_storage');
            }

            $form = $this->createStoragecardForm($storagecard, 'remove');
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->storagecardService->removeStoragecard($storagecard);

                $this->addFlash('success', 'La fiche de stockage a été supprimée avec succès.');
                return $this->redirectToRoute('admin_storage');
            }

            return $this->renderStorageForm(
                $form,
                'remove',
                false,
                ['storagecards' => $storagecards, 'id' => $id]
            );
        }
        catch (ForeignKeyConstraintViolationException $fkcve) {
            $this->addFlash('error',
                'Impossible de supprimer cette fiche. Il existe un historique d\'utilisation de ce produit pour un utilisateur. ' .
                'Archivez cette fiche ou contactez votre administrateur.');
            return $this->redirectToRoute('admin_storage');
        }
        catch (Exception $e) {
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

        return $this->render('admin/storage.html.twig', $params);
    }
}