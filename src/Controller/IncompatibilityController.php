<?php

namespace App\Controller;

use App\Entity\Chimicalproduct;
use App\Entity\IncompatibilityRequest;
use App\Entity\Shelvingunit;
use App\Entity\Storagecard;
use App\Form\IncompatibilityRequestType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class IncompatibilityController extends AbstractController
{
    #[Route('/incompatibility/request/{productId}/{shelvingUnitId}', name: 'incompatibility_request')]
    public function requestOverride(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        MailerInterface $mailer,
        int $productId,
        int $shelvingUnitId
    ): Response {
        // Récupérer le produit et l'emplacement
        $product = $entityManager->getRepository(Chimicalproduct::class)->find($productId);
        $shelvingUnit = $entityManager->getRepository(Shelvingunit::class)->find($shelvingUnitId);

        if (!$product || !$shelvingUnit) {
            $this->addFlash('error', 'Produit ou emplacement non trouvé.');
            return $this->redirectToRoute('inventory');
        }

        // Récupérer les produits incompatibles déjà présents à cet emplacement
        $incompatibleProducts = $this->findIncompatibleProducts($product, $shelvingUnit, $entityManager);

        if (empty($incompatibleProducts)) {
            $this->addFlash('error', 'Aucune incompatibilité détectée avec cet emplacement.');
            return $this->redirectToRoute('inventory');
        }

        // Créer le formulaire de demande
        $form = $this->createForm(IncompatibilityRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            // Créer la demande de dérogation
            $incompatibilityRequest = new IncompatibilityRequest();
            $incompatibilityRequest->setRequester($tokenStorage->getToken()->getUser());
            $incompatibilityRequest->setProduct($product);
            $incompatibilityRequest->setShelvingUnit($shelvingUnit);

            // Liste des produits incompatibles
            $incompatibleProductsList = array_map(function($p) {
                return $p->getNameChimicalproduct();
            }, $incompatibleProducts);

            $incompatibilityRequest->setIncompatibleWith(implode(', ', $incompatibleProductsList));
            $incompatibilityRequest->setReason($formData['reason']);
            $incompatibilityRequest->setIsUrgent($formData['urgencyLevel']);

            // Sauvegarder la demande
            $entityManager->persist($incompatibilityRequest);
            $entityManager->flush();

            // Notifier les administrateurs
            $this->notifyAdmins($incompatibilityRequest, $mailer, $entityManager);

            $this->addFlash('success', 'Votre demande de dérogation a été soumise. Un administrateur l\'examinera prochainement.');
            return $this->redirectToRoute('inventory');
        }

        return $this->render('incompatibility/request.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'shelvingUnit' => $shelvingUnit,
            'incompatibleProducts' => $incompatibleProducts
        ]);
    }

    #[Route('/admin/incompatibility/list', name: 'admin_incompatibility_list')]
    public function listRequests(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $entityManager->getRepository(IncompatibilityRequest::class);
        $pendingRequests = $repository->findBy(['status' => 'pending'], ['requestDate' => 'DESC']);
        $processedRequests = $repository->findBy(
            ['status' => ['approved', 'rejected']],
            ['responseDate' => 'DESC'],
            20 // Limiter aux 20 dernières demandes traitées
        );

        return $this->render('incompatibility/admin_list.html.twig', [
            'pendingRequests' => $pendingRequests,
            'processedRequests' => $processedRequests
        ]);
    }

    #[Route('/admin/incompatibility/process/{id}/{action}', name: 'admin_incompatibility_process')]
    public function processRequest(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        MailerInterface $mailer,
        int $id,
        string $action
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $incompatibilityRequest = $entityManager->getRepository(IncompatibilityRequest::class)->find($id);

        if (!$incompatibilityRequest) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('admin_incompatibility_list');
        }

        // Vérifier que la demande est en attente
        if ($incompatibilityRequest->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette demande a déjà été traitée.');
            return $this->redirectToRoute('admin_incompatibility_list');
        }

        $comment = $request->request->get('comment', '');

        // Mettre à jour la demande
        $incompatibilityRequest->setStatus($action === 'approve' ? 'approved' : 'rejected');
        $incompatibilityRequest->setResponseDate(new DateTime());
        $incompatibilityRequest->setResponder($tokenStorage->getToken()->getUser());
        $incompatibilityRequest->setResponseComment($comment);

        $entityManager->flush();

        // Notifier l'utilisateur
        $this->notifyUser($incompatibilityRequest, $mailer);

        $this->addFlash('success', 'La demande a été ' . ($action === 'approve' ? 'approuvée' : 'rejetée') . ' avec succès.');
        return $this->redirectToRoute('admin_incompatibility_list');
    }

    /**
     * Trouve les produits incompatibles déjà présents à un emplacement
     */
    private function findIncompatibleProducts(
        Chimicalproduct $product,
        Shelvingunit $shelvingUnit,
        EntityManagerInterface $entityManager
    ): array {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $existingStoragecards = $repositoryStoragecard->loadStorageCardByShelvingunit($shelvingUnit);

        $incompatibleProducts = [];
        $repositoryControlByType = $entityManager->getRepository(\App\Entity\Controlbytype::class);

        // Récupérer les types du produit qu'on veut déplacer
        $types1 = $product->getIdType();

        foreach ($existingStoragecards as $card) {
            $existingProduct = $card->getIdChimicalproduct();
            $types2 = $existingProduct->getIdType();

            // Vérifier les combinaisons de types pour incompatibilité
            $isIncompatible = false;

            foreach ($types1 as $type1) {
                foreach ($types2 as $type2) {
                    $control = $repositoryControlByType->findOneBy([
                        'idType1' => $type1->getIdType(),
                        'idType2' => $type2->getIdType()
                    ]);

                    // Si une règle d'incompatibilité est trouvée
                    if ($control && !$control->getIscompatible()) {
                        $isIncompatible = true;
                        break 2; // Sortir des deux boucles
                    }
                }
            }

            if ($isIncompatible) {
                $incompatibleProducts[] = $existingProduct;
            }
        }

        return $incompatibleProducts;
    }

    /**
     * Notifie les administrateurs d'une nouvelle demande
     */
    private function notifyAdmins(
        IncompatibilityRequest $request,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ): void {
        // Récupérer les emails des administrateurs
        $adminRepository = $entityManager->getRepository(\App\Entity\User::class);
        $admins = $adminRepository->loadAdministrators();

        if (empty($admins)) {
            return; // Pas d'administrateurs à notifier
        }

        $adminEmails = array_map(function($admin) {
            return $admin->getMail();
        }, $admins);

        // Créer l'email
        $email = (new Email())
            ->from('noreply@khemeia.fr')
            ->to(...$adminEmails)
            ->subject('Nouvelle demande de dérogation d\'incompatibilité')
            ->html($this->renderView('emails/incompatibility_notification.html.twig', [
                'request' => $request
            ]));

        try {
            // Envoyer l'email
            $mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre le flux
            error_log('Erreur lors de l\'envoi de la notification aux administrateurs: ' . $e->getMessage());
        }
    }

    /**
     * Notifie l'utilisateur du traitement de sa demande
     */
    private function notifyUser(IncompatibilityRequest $request, MailerInterface $mailer): void
    {
        if (!$request->getRequester() || !$request->getRequester()->getMail()) {
            return; // Impossible d'envoyer sans destinataire
        }

        $userEmail = $request->getRequester()->getMail();

        // Créer l'email
        $email = (new Email())
            ->from('noreply@khemeia.fr')
            ->to($userEmail)
            ->subject('Réponse à votre demande de dérogation d\'incompatibilité')
            ->html($this->renderView('emails/incompatibility_response.html.twig', [
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

    /**
     * Affiche les demandes de dérogation de l'utilisateur actuel
     */
    #[Route('/incompatibility/my-requests', name: 'user_incompatibility_requests')]
    public function userRequests(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()->getUser();
        $repository = $entityManager->getRepository(IncompatibilityRequest::class);

        // Récupérer les demandes de l'utilisateur actuel, triées par statut
        $pendingRequests = $repository->findBy(
            ['requester' => $user, 'status' => 'pending'],
            ['requestDate' => 'DESC']
        );

        // Récupère uniquement les demandes approuvées qui n'ont pas encore été utilisées
        $approvedRequests = $repository->findBy(
            ['requester' => $user, 'status' => 'approved', 'isUsed' => false],
            ['responseDate' => 'DESC']
        );

        // Pour les demandes rejetées, on peut tout afficher
        $rejectedRequests = $repository->findBy(
            ['requester' => $user, 'status' => 'rejected'],
            ['responseDate' => 'DESC']
        );

        // Récupérer les demandes approuvées qui ont été utilisées (pour information)
        $usedRequests = $repository->findBy(
            ['requester' => $user, 'status' => 'approved', 'isUsed' => true],
            ['responseDate' => 'DESC']
        );

        return $this->render('incompatibility/user_requests.html.twig', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
            'usedRequests' => $usedRequests
        ]);
    }

    /**
     * Crée une fiche de stockage à partir d'une demande de dérogation approuvée
     */
    #[Route('/incompatibility/create-storage/{id}', name: 'create_storage_from_request')]
    public function createStorageFromRequest(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        int $id
    ): Response
    {
        // Récupérer la demande de dérogation
        $incompatibilityRequest = $entityManager->getRepository(IncompatibilityRequest::class)->find($id);

        if (!$incompatibilityRequest) {
            $this->addFlash('error', 'Demande non trouvée.');
            return $this->redirectToRoute('user_incompatibility_requests');
        }

        // Vérifier que la demande appartient à l'utilisateur actuel
        $currentUser = $tokenStorage->getToken()->getUser();
        if ($incompatibilityRequest->getRequester() !== $currentUser) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette demande.');
            return $this->redirectToRoute('user_incompatibility_requests');
        }

        // Vérifier que la demande est approuvée
        if ($incompatibilityRequest->getStatus() !== 'approved') {
            $this->addFlash('error', 'Seules les demandes approuvées peuvent être utilisées pour créer une fiche de stockage.');
            return $this->redirectToRoute('user_incompatibility_requests');
        }

        // Vérifier que la demande n'a pas déjà été utilisée
        if ($incompatibilityRequest->getIsUsed()) {
            $this->addFlash('error', 'Cette demande a déjà été utilisée pour créer une fiche de stockage.');
            return $this->redirectToRoute('user_incompatibility_requests');
        }

        // Stocker temporairement l'ID de la demande dans la session
        $request->getSession()->set('incompatibility_request_id', $id);

        // Rediriger vers le formulaire de création de fiche de stockage pré-rempli
        return $this->redirectToRoute('inventory_storage_from_request', [
            'productId' => $incompatibilityRequest->getProduct()->getIdChimicalproduct(),
            'shelvingUnitId' => $incompatibilityRequest->getShelvingUnit()->getIdShelvingunit()
        ]);
    }

}