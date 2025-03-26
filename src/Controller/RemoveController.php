<?php

namespace App\Controller;

use App\Service\MailService;
use App\Service\RemoveService;
use App\Service\SearchService;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RemoveController extends AbstractSearchController
{
    public function __construct(
        private readonly RemoveService $removeService,
        private readonly MailService $mailService,
        private readonly TokenStorageInterface $tokenStorage,
        SearchService $searchService
    ) {
        parent::__construct($searchService);
    }

    #[Route('/remove', name: 'remove')]
    public function index(Request $request): Response
    {
        try {
            $templateVars = $this->handleSearch($request);
            return $this->render($this->getTemplate(), $templateVars);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    #[Route('/remove/{id}', name: 'remove_quantity')]
    public function removeQuantity(int $id, Request $request): Response
    {
        try {
            $storagecard = $this->searchService->findStorageCard($id);

            if (!$storagecard) {
                $this->addFlash('error', 'Fiche de stockage non trouvée.');
                return $this->redirectToRoute($this->getRedirectRoute());
            }

            // Vérifier si le produit est sur un autre site
            $user = $this->getUser();
            $userSite = $user->getIdSite();
            $productSite = $storagecard->getIdShelvingunit()->getIdCupboard()->getIdStock()->getIdSite();

            // Si le produit est sur un autre site, rediriger vers la demande de retrait externe
            if ($userSite->getIdSite() !== $productSite->getIdSite()) {
                $this->addFlash('info', 'Ce produit appartient à un autre site. Vous allez être redirigé vers le formulaire de demande de retrait externe.');

                return $this->redirectToRoute('external_withdrawal_request', [
                    'sourceSite' => $productSite->getIdSite(),
                    'productId' => $storagecard->getIdStoragecard()
                ]);
            }

            $result = $this->removeService->handleRemoveForm($request, $storagecard);

            if ($result['form'] && $result['processed'] === false) {
                return $this->render('remove/remove.html.twig', [
                    'form' => $result['form']->createView(),
                    'id' => $id,
                    'storagecard' => $storagecard
                ]);
            }

            if ($result['success']) {
                $this->addFlash('success', 'La quantité de produit a bien été retirée.');
            }

            return $this->redirectToRoute('remove_quantity', ['id' => $id]);
        } catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->redirectToRoute('remove_quantity', ['id' => $id]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    #[Route('/remove/ask/{id}', name: 'remove_ask')]
    public function askForRemove(int $id): Response
    {
        try {
            $storagecard = $this->searchService->findStorageCard($id);

            if (!$storagecard) {
                $this->addFlash('error', 'Fiche de stockage non trouvée.');
                return $this->redirectToRoute($this->getRedirectRoute());
            }

            $user = $this->getUser();
            $site = $storagecard->getIdShelvingunit()->getIdCupboard()->getIdStock()->getIdSite()->getIdSite();

            $result = $this->mailService->sendRemoveRequestEmail($site, $user, $storagecard);

            if ($result) {
                $this->addFlash('success', 'Votre demande a été transmise au responsable.');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de l\'email.');
            }

            return $this->redirectToRoute('remove_quantity', ['id' => $id]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplate(): string
    {
        return 'remove/index.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectRoute(): string
    {
        return 'home_page';
    }
}