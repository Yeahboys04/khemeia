<?php

namespace App\Controller;

use App\Entity\Analysisfile;
use App\Entity\Securityfile;
use App\Entity\Storagecard;
use App\Form\StoragecardRespType;
use App\Service\FileService;
use App\Service\InventoryService;
use App\Service\StoragecardService;
use DateTimeImmutable;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Classe abstraite pour factoriser le code commun entre InventoryController et AdminStorageController
 */
abstract class AbstractStorageController extends AbstractController
{
    public function __construct(
        protected readonly StoragecardService $storagecardService,
        protected readonly FileService $fileService,
        protected readonly InventoryService $inventoryService,
        protected readonly TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Crée un formulaire pour une fiche de stockage avec les options appropriées
     */
    protected function createStoragecardForm(
        Storagecard $storagecard,
        string $operationType,
        bool $fromDerogation = false
    ): \Symfony\Component\Form\FormInterface {
        $user = $this->getUser();

        $options = [
            'method' => 'POST',
            'idSite' => $user->getIdSite()->getIdSite(),
        ];

        if ($operationType === 'duplicate' || $operationType === 'copy') {
            $options['operation_type'] = 'copy';
        } elseif ($operationType !== 'remove') {
            $options['operation_type'] = $operationType;
        }

        if ($fromDerogation) {
            $options['from_derogation'] = true;
        }

        return $this->createForm(StoragecardRespType::class, $storagecard, $options);
    }

    /**
     * Vérifie la compatibilité entre un emplacement et un produit
     *
     * @return Response|null Response en cas d'incompatibilité, null si compatible
     */
    protected function checkCompatibility(
        $idShelvingunit,
        $chimicalproduct,
        bool $overrideCheck,
        $form,
        string $operationType
    ): ?Response {
        try {
            $this->inventoryService->checkCompatibility($idShelvingunit, $chimicalproduct, $overrideCheck);
            return null;
        } catch (LogicException $le) {
            // Si l'utilisateur n'est pas admin, redirection vers demande de dérogation
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('incompatibility_request', [
                    'productId' => $chimicalproduct->getIdChimicalproduct(),
                    'shelvingUnitId' => $idShelvingunit->getIdShelvingunit()
                ]);
            }

            // Pour les admins, possibilité de dérogation
            $this->addFlash('warning',
                $le->getMessage() . ' En tant qu\'administrateur, vous pouvez contourner cette restriction en cochant l\'option de dérogation.');

            return $this->renderStorageForm(
                $form,
                $operationType,
                true
            );
        }
    }

    /**
     * Traite les fichiers uploadés pour une fiche de stockage
     */
    protected function handleFileUploads(
        $form,
        Storagecard $storagecard,
        ?Securityfile $oldSecurityFile = null,
        ?Analysisfile $oldAnalysisFile = null
    ): Storagecard {
        $securityFile = $form->get('uploadedSecurityFile')->getData();
        $analysisFile = $form->get('uploadedAnalysisFile')->getData();

        if ($securityFile !== null) {
            $securityfile = $this->fileService->processSecurityFile($securityFile);
            $storagecard->setIdSecurityfile($securityfile);
        } elseif ($oldSecurityFile !== null) {
            $storagecard->setIdSecurityfile($oldSecurityFile);
        }

        if ($analysisFile !== null) {
            $analysisFileEntity = $this->fileService->processAnalysisFile($analysisFile);
            $storagecard->setIdAnalysisfile($analysisFileEntity);
        } elseif ($oldAnalysisFile !== null) {
            $storagecard->setIdAnalysisfile($oldAnalysisFile);
        }

        return $storagecard;
    }

    /**
     * Traite une soumission de formulaire pour une fiche de stockage
     */
    protected function processStoragecardForm(
        Request $request,
                $form,
        Storagecard $storagecard,
        string $operationType,
        bool $moveCheck = true,
        ?Securityfile $oldSecurityFile = null,
        ?Analysisfile $oldAnalysisFile = null
    ): ?Response
    {
        try {
            // Récupération de l'état physique
            if ($form->has('stateType')) {
                $stateType = $form->get('stateType')->getData();
                $storagecard->setStateType($stateType);
            }

            $idShelvingunit = $form->get('idShelvingunit')->getData();
            $chimicalproduct = $form->get('idChimicalproduct')->getData();

            // Vérifier l'override des incompatibilités pour les admins
            $overrideCheck = $request->request->has('override_incompatibility')
                && $request->request->get('override_incompatibility') === "1";

            // Vérification de compatibilité si nécessaire
            if ($moveCheck) {
                $compatibilityResponse = $this->checkCompatibility(
                    $idShelvingunit,
                    $chimicalproduct,
                    $overrideCheck,
                    $form,
                    $operationType
                );

                if ($compatibilityResponse instanceof Response) {
                    return $compatibilityResponse;
                }
            }

            // Traitement des fichiers
            $storagecard = $this->handleFileUploads(
                $form,
                $storagecard,
                $oldSecurityFile,
                $oldAnalysisFile
            );

            // Sauvegarde de la fiche et création de l'historique
            $storagecard = $this->storagecardService->saveStoragecard(
                $storagecard,
                $operationType,
                $moveCheck
            );

            // Message de succès
            $this->addSuccessMessages($storagecard, $operationType, $overrideCheck && $moveCheck);

            return null;
        } catch (FileException $e) {
            // Amélioration du message d'erreur pour les problèmes de fichier
            if (strpos($e->getMessage(), 'maxSize') !== false || strpos($e->getMessage(), 'trop volumineux') !== false) {
                $this->addFlash('error', 'Le fichier est trop volumineux. La taille maximale autorisée est de 3 Mo.');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors du traitement du fichier: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Affiche les messages de succès appropriés
     */
    protected function addSuccessMessages(
        Storagecard $storagecard,
        string $operationType,
        bool $overrideUsed
    ): void {
        if ($operationType === 'modify') {
            $this->addFlash('success', 'La fiche de stockage a été modifiée avec succès.');
        } elseif ($operationType === 'duplicate' || $operationType === 'copy') {
            $this->addFlash('success', 'La fiche de stockage a été dupliquée avec succès.');
        } else {
            $this->addFlash('success',
                'La fiche de stockage numéro ' . $storagecard->getIdStoragecard() . ' a été créée avec succès.');
        }

        if ($this->isGranted('ROLE_ADMIN') && $overrideUsed) {
            $this->addFlash('info', 'Vous avez utilisé votre droit d\'administrateur pour ignorer les incompatibilités détectées.');
        }
    }

    /**
     * Méthode abstraite pour le rendu des formulaires de fiche de stockage
     * À implémenter dans les classes enfants
     */
    abstract protected function renderStorageForm(
        $form,
        string $operationType,
        bool $incompatibilityDetected = false,
        array $additionalParams = []
    ): Response;
}