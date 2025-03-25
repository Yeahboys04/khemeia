<?php

namespace App\Controller;

use App\Entity\Analysisfile;
use App\Entity\Chimicalproduct;
use App\Entity\ExternalWithdrawalRequest;
use App\Entity\Movedhistory;
use App\Entity\Securityfile;
use App\Entity\Site;
use App\Entity\Storagecard;
use App\Form\ExternalWithdrawalRequestType;
use App\Form\StoragecardRespType;
use App\Repository\ExternalWithdrawalRequestRepository;
use App\Repository\StoragecardRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExternalWithdrawalController extends AbstractController
{
    /**
     * Affiche le formulaire de demande de retrait externe
     */
    #[Route('/withdrawal/external', name: 'external_withdrawal_request')]
    public function requestForm(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        MailerInterface $mailer
    ): Response {
        $user = $tokenStorage->getToken()->getUser();
        $currentSite = $user->getIdSite();

        $externalRequest = new ExternalWithdrawalRequest();
        $externalRequest->setRequester($user);
        $externalRequest->setDestinationSite($currentSite);

        // Récupérer les paramètres de l'URL (pour le pré-remplissage)
        $sourceSiteId = $request->query->get('sourceSite');
        $productId = $request->query->get('productId');

        // Préparer le produit si spécifié
        $sourceProduct = null;
        if ($productId) {
            $sourceProduct = $entityManager->getRepository(Storagecard::class)->find($productId);
            if ($sourceProduct) {
                $externalRequest->setSourceStoragecard($sourceProduct);

                // Si le site source n'est pas spécifié mais que nous avons le produit,
                // déduire le site source à partir du produit
                if (!$sourceSiteId && $sourceProduct) {
                    $sourceSiteId = $sourceProduct->getIdShelvingunit()
                        ->getIdCupboard()
                        ->getIdStock()
                        ->getIdSite()
                        ->getIdSite();
                }
            }
        }

        // Créer le formulaire avec les options pour les sites et produits spécifiés
        $form = $this->createForm(ExternalWithdrawalRequestType::class, $externalRequest, [
            'source_site_id' => $sourceSiteId,
            'entity_manager' => $entityManager
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la fiche de stockage source
            $sourceStoragecard = $externalRequest->getSourceStoragecard();

            // Vérifier si la quantité demandée est disponible
            if ($sourceStoragecard->getStockquantity() < $externalRequest->getRequestedQuantity()) {
                $this->addFlash('error', 'La quantité demandée est supérieure à la quantité disponible.');
                return $this->redirectToRoute('external_withdrawal_request');
            }

            // Définir le site source à partir de la fiche de stockage
            $sourceSite = $sourceStoragecard->getIdShelvingunit()->getIdCupboard()->getIdStock()->getIdSite();
            $externalRequest->setSourceSite($sourceSite);

            // Enregistrer la demande
            $entityManager->persist($externalRequest);
            $entityManager->flush();

            // Notifier les responsables du site source
            $this->notifySourceSiteResponsibles($externalRequest, $mailer, $entityManager);

            $this->addFlash('success', 'Votre demande de retrait a été soumise. Les responsables du site source examineront votre demande.');
            return $this->redirectToRoute('user_withdrawal_requests');
        }

        return $this->render('external_withdrawal/request_form.html.twig', [
            'form' => $form->createView(),
            'currentSite' => $currentSite,
            'prefilledSourceSite' => $sourceSiteId ? true : false,
            'prefilledProduct' => $productId ? true : false
        ]);
    }

    /**
     * Affiche la liste des demandes pour l'utilisateur courant
     */
    #[Route('/withdrawal/my-requests', name: 'user_withdrawal_requests')]
    public function userRequests(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        $user = $tokenStorage->getToken()->getUser();
        $repository = $entityManager->getRepository(ExternalWithdrawalRequest::class);

        // Récupérer les demandes de l'utilisateur, triées par statut
        $pendingRequests = $repository->findPendingRequestsByUser($user);
        $approvedRequests = $repository->findBy(
            ['requester' => $user, 'status' => 'approved', 'isCompleted' => false],
            ['responseDate' => 'DESC']
        );
        $completedRequests = $repository->findBy(
            ['requester' => $user, 'isCompleted' => true],
            ['responseDate' => 'DESC']
        );
        $rejectedRequests = $repository->findBy(
            ['requester' => $user, 'status' => 'rejected'],
            ['responseDate' => 'DESC']
        );

        return $this->render('external_withdrawal/user_requests.html.twig', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'completedRequests' => $completedRequests,
            'rejectedRequests' => $rejectedRequests
        ]);
    }


    /**
     * Affiche la liste des demandes à traiter pour le site de l'utilisateur
     */
    #[Route('/withdrawal/manage-requests', name: 'manage_withdrawal_requests')]
    public function manageRequests(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        // Vérifier que l'utilisateur est responsable ou administrateur
        $user = $tokenStorage->getToken()->getUser();
        $userStatus = $user->getIdStatus()->getNameStatus();

        if ($userStatus !== 'administrateur' && $userStatus !== 'responsable') {
            // Rediriger vers la page d'accueil avec un message d'erreur
            $this->addFlash('error', 'Vous n\'avez pas les droits pour accéder à cette page.');
            return $this->redirectToRoute('home_page');
        }

        $site = $user->getIdSite();

        $repository = $entityManager->getRepository(ExternalWithdrawalRequest::class);
        $pendingRequests = $repository->findPendingRequestsForSite($site);
        $processedRequests = $repository->findProcessedRequestsForSite($site);

        return $this->render('external_withdrawal/manage_requests.html.twig', [
            'pendingRequests' => $pendingRequests,
            'processedRequests' => $processedRequests
        ]);
    }

    /**
     * Traite une demande (approbation ou rejet)
     */
    #[Route('/withdrawal/process-request/{id}/{action}', name: 'process_withdrawal_request')]
    public function processRequest(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        MailerInterface $mailer,
        int $id,
        string $action
    ): Response {
        // Vérifier que l'utilisateur est responsable ou administrateur
        $user = $tokenStorage->getToken()->getUser();
        $userStatus = $user->getIdStatus()->getNameStatus();

        if ($userStatus !== 'administrateur' && $userStatus !== 'responsable') {
            // Rediriger vers la page d'accueil avec un message d'erreur
            $this->addFlash('error', 'Vous n\'avez pas les droits pour accéder à cette page.');
            return $this->redirectToRoute('home_page');
        }

        $withdrawalRequest = $entityManager->getRepository(ExternalWithdrawalRequest::class)->find($id);

        if (!$withdrawalRequest) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('manage_withdrawal_requests');
        }

        // Vérifier que la demande est en attente
        if ($withdrawalRequest->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('manage_withdrawal_requests');
        }

        // Vérifier que l'utilisateur est responsable du site source ou administrateur
        if ($user->getIdSite()->getIdSite() != $withdrawalRequest->getSourceSite()->getIdSite() && $userStatus !== 'administrateur') {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à traiter cette demande.');
            return $this->redirectToRoute('manage_withdrawal_requests');
        }

        $comment = $request->request->get('comment', '');

        // Mettre à jour la demande
        $withdrawalRequest->setStatus($action === 'approve' ? 'approved' : 'rejected');
        $withdrawalRequest->setResponseDate(new DateTime());
        $withdrawalRequest->setResponder($user);
        $withdrawalRequest->setResponseComment($comment);

        // Si la demande est approuvée, réserver la quantité
        if ($action === 'approve') {
            $sourceStoragecard = $withdrawalRequest->getSourceStoragecard();
            $currentQuantity = $sourceStoragecard->getStockquantity();

            // Vérifier à nouveau si la quantité est disponible
            if ($currentQuantity < $withdrawalRequest->getRequestedQuantity()) {
                $this->addFlash('error', 'La quantité demandée n\'est plus disponible.');
                return $this->redirectToRoute('manage_withdrawal_requests');
            }

            // Réserver la quantité (déduire du stock)
            $sourceStoragecard->setStockquantity($currentQuantity - $withdrawalRequest->getRequestedQuantity());
            $entityManager->persist($sourceStoragecard);
        }

        $entityManager->flush();

        // Notifier l'utilisateur
        $this->notifyRequester($withdrawalRequest, $mailer);

        $this->addFlash('success', 'La demande a été ' . ($action === 'approve' ? 'approuvée' : 'rejetée') . ' avec succès.');
        return $this->redirectToRoute('manage_withdrawal_requests');
    }

    /**
     * Crée une fiche de stockage à partir d'une demande approuvée
     */
    #[Route('/withdrawal/complete-request/{id}', name: 'complete_withdrawal_request')]
    public function completeRequest(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        MailerInterface $mailer,
        int $id
    ): Response {
        $withdrawalRequest = $entityManager->getRepository(ExternalWithdrawalRequest::class)->find($id);

        if (!$withdrawalRequest) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('user_withdrawal_requests');
        }

        // Vérifier que l'utilisateur est le demandeur
        $user = $tokenStorage->getToken()->getUser();
        if ($withdrawalRequest->getRequester()->getIdUser() != $user->getIdUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à compléter cette demande.');
            return $this->redirectToRoute('user_withdrawal_requests');
        }

        // Vérifier que la demande est approuvée et non complétée
        if ($withdrawalRequest->getStatus() !== 'approved' || $withdrawalRequest->getIsCompleted()) {
            $this->addFlash('error', 'Cette demande ne peut pas être complétée.');
            return $this->redirectToRoute('user_withdrawal_requests');
        }

        // Récupérer la fiche source
        $sourceStoragecard = $withdrawalRequest->getSourceStoragecard();
        $chimicalProduct = $sourceStoragecard->getIdChimicalproduct();

        // Chercher si une fiche de stockage existe déjà pour ce produit sur le site destination
        $storageCardRepository = $entityManager->getRepository(Storagecard::class);
        $existingStoragecards = $storageCardRepository->findBy([
            'idChimicalproduct' => $chimicalProduct,
            'isarchived' => false
        ]);

        $destinationStoragecard = null;

        // Filtrer pour trouver une fiche dans le site de destination
        foreach ($existingStoragecards as $card) {
            $cardSite = $card->getIdShelvingunit()->getIdCupboard()->getIdStock()->getIdSite();
            if ($cardSite->getIdSite() == $withdrawalRequest->getDestinationSite()->getIdSite()) {
                $destinationStoragecard = $card;
                break;
            }
        }

        // Si une fiche existe, mettre à jour la quantité
        if ($destinationStoragecard) {
            $currentQuantity = $destinationStoragecard->getStockquantity() ?: 0;
            $destinationStoragecard->setStockquantity($currentQuantity + $withdrawalRequest->getRequestedQuantity());

            // Mettre à jour la référence dans la demande
            $withdrawalRequest->setDestinationStoragecard($destinationStoragecard);
        } else {
            // Créer une nouvelle fiche de stockage
            $destinationStoragecard = new Storagecard();
            $destinationStoragecard->setIdChimicalproduct($chimicalProduct);

            // Trouver un emplacement approprié sur le site de destination
            // (Idéalement ici, vous implémenteriez une logique pour trouver un emplacement compatible)
            // Pour simplifier, nous demandons à l'utilisateur de choisir un emplacement

            // Rediriger vers un formulaire pour choisir l'emplacement
            $request->getSession()->set('withdrawal_request_id', $id);
            return $this->redirectToRoute('select_location_for_withdrawal', [
                'requestId' => $id
            ]);
        }

        // Marquer la demande comme complétée
        $withdrawalRequest->setIsCompleted(true);
        $withdrawalRequest->setDestinationStoragecard($destinationStoragecard);

        $entityManager->persist($destinationStoragecard);
        $entityManager->persist($withdrawalRequest);
        $entityManager->flush();

        // Enregistrer un mouvement pour traçabilité
        $movedHistory = new Movedhistory();
        $movedHistory->setMovedate(new DateTime());
        $movedHistory->setIdShelvingunit($destinationStoragecard->getIdShelvingunit());
        $movedHistory->setIdStoragecard($destinationStoragecard);
        $movedHistory->setIdUser($user);

        $entityManager->persist($movedHistory);
        $entityManager->flush();

        $this->addFlash('success', 'La demande a été complétée avec succès et le produit est maintenant disponible sur votre site.');
        return $this->redirectToRoute('user_withdrawal_requests');
    }

    /**
     * Formulaire pour sélectionner l'emplacement de stockage
     */
    #[Route('/withdrawal/select-location/{requestId}', name: 'select_location_for_withdrawal')]
    public function selectLocation(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        int $requestId
    ): Response {
        $withdrawalRequest = $entityManager->getRepository(ExternalWithdrawalRequest::class)->find($requestId);

        if (!$withdrawalRequest) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('user_withdrawal_requests');
        }

        // Vérifier que l'utilisateur est le demandeur
        $user = $tokenStorage->getToken()->getUser();
        if ($withdrawalRequest->getRequester()->getIdUser() != $user->getIdUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à compléter cette demande.');
            return $this->redirectToRoute('user_withdrawal_requests');
        }

        // Créer une nouvelle fiche de stockage
        $sourceStoragecard = $withdrawalRequest->getSourceStoragecard();
        $chimicalProduct = $sourceStoragecard->getIdChimicalproduct();

        $destinationStoragecard = new Storagecard();
        $destinationStoragecard->setIdChimicalproduct($chimicalProduct);
        $destinationStoragecard->setCapacity($sourceStoragecard->getCapacity());
        $destinationStoragecard->setStockquantity($withdrawalRequest->getRequestedQuantity());
        $destinationStoragecard->setPurity($sourceStoragecard->getPurity());
        $destinationStoragecard->setSerialnumber($sourceStoragecard->getSerialnumber());
        $destinationStoragecard->setTemperature($sourceStoragecard->getTemperature());
        $destinationStoragecard->setOpendate(new DateTime());
        $destinationStoragecard->setExpirationdate($sourceStoragecard->getExpirationdate());
        $destinationStoragecard->setIsarchived(false);
        $destinationStoragecard->setIsrisked($sourceStoragecard->getIsrisked());
        $destinationStoragecard->setIspublished(true);
        $destinationStoragecard->setIdProperty($sourceStoragecard->getIdProperty());
        $destinationStoragecard->setIdSupplier($sourceStoragecard->getIdSupplier());
        $destinationStoragecard->setReference($sourceStoragecard->getReference());
        $destinationStoragecard->setStateType($sourceStoragecard->getStateType());
        $destinationStoragecard->setCreationDate(new DateTime());

        // Si le produit source a des fichiers associés, les copier
        if ($sourceStoragecard->getIdSecurityfile()) {
            $securityFile = $sourceStoragecard->getIdSecurityfile();
            $destinationStoragecard->setIdSecurityfile($securityFile);
        }

        if ($sourceStoragecard->getIdAnalysisfile()) {
            $analysisFile = $sourceStoragecard->getIdAnalysisfile();
            $destinationStoragecard->setIdAnalysisfile($analysisFile);
        }

        // Créer un formulaire pour choisir l'emplacement
        $form = $this->createForm(StoragecardRespType::class, $destinationStoragecard, [
            'method' => 'POST',
            'idSite' => $user->getIdSite()->getIdSite()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer la fiche de stockage
            $entityManager->persist($destinationStoragecard);

            // Mettre à jour la demande
            $withdrawalRequest->setIsCompleted(true);
            $withdrawalRequest->setDestinationStoragecard($destinationStoragecard);
            $entityManager->persist($withdrawalRequest);
            $entityManager->flush();

            // Enregistrer un mouvement pour traçabilité
            $movedHistory = new Movedhistory();
            $movedHistory->setMovedate(new DateTime());
            $movedHistory->setIdShelvingunit($destinationStoragecard->getIdShelvingunit());
            $movedHistory->setIdStoragecard($destinationStoragecard);
            $movedHistory->setIdUser($user);

            $entityManager->persist($movedHistory);
            $entityManager->flush();

            $this->addFlash('success', 'La demande a été complétée avec succès et le produit est maintenant disponible sur votre site.');
            return $this->redirectToRoute('user_withdrawal_requests');
        }

        return $this->render('external_withdrawal/select_location.html.twig', [
            'form' => $form->createView(),
            'request' => $withdrawalRequest,
            'product' => $chimicalProduct
        ]);
    }

    /**
     * Notifie les responsables du site source d'une nouvelle demande
     */
    private function notifySourceSiteResponsibles(
        ExternalWithdrawalRequest $request,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ): void {
        // Récupérer les responsables du site source
        $userRepository = $entityManager->getRepository(\App\Entity\User::class);
        $responsibles = $userRepository->loadSupervisors($request->getSourceSite());

        if (empty($responsibles)) {
            // Pas de responsables à notifier
            return;
        }

        $emails = array_map(function($responsible) {
            return $responsible->getMail();
        }, $responsibles);

        // Créer l'email
        $email = (new Email())
            ->from('noreply@khemeia.fr')
            ->to(...$emails)
            ->subject('Nouvelle demande de retrait de produit')
            ->html($this->renderView('emails/external_withdrawal_notification.html.twig', [
                'request' => $request
            ]));

        try {
            // Envoyer l'email
            $mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre le flux
            error_log('Erreur lors de l\'envoi de la notification aux responsables: ' . $e->getMessage());
        }
    }

    /**
     * Notifie l'utilisateur du traitement de sa demande
     */
    private function notifyRequester(
        ExternalWithdrawalRequest $request,
        MailerInterface $mailer
    ): void {
        if (!$request->getRequester() || !$request->getRequester()->getMail()) {
            // Impossible d'envoyer sans destinataire
            return;
        }

        $userEmail = $request->getRequester()->getMail();

        // Créer l'email
        $email = (new Email())
            ->from('noreply@khemeia.fr')
            ->to($userEmail)
            ->subject('Réponse à votre demande de retrait de produit')
            ->html($this->renderView('emails/external_withdrawal_response.html.twig', [
                'request' => $request
            ]));

        try {
            // Envoyer l'email
            $mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre le flux
            error_log('Erreur lors de l\'envoi de la notification à l\'utilisateur: ' . $e->getMessage());
        }
    }
}