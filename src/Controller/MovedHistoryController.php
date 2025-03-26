<?php

namespace App\Controller;

use App\Service\HistoryService;
use App\Service\SearchService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MovedHistoryController extends AbstractSearchController
{
    public function __construct(
        private readonly HistoryService $historyService,
        SearchService $searchService
    ) {
        parent::__construct($searchService);
    }

    #[Route('/history/{id}', name: 'moved_history')]
    public function movedHistory(int $id): Response
    {
        try {
            $storageCard = $this->searchService->findStorageCard($id);

            if (!$storageCard) {
                $this->addFlash('error', 'Fiche de stockage non trouvée.');
                return $this->redirectToRoute($this->getRedirectRoute());
            }

            $movedHistory = $this->historyService->getMovedHistory($storageCard->getIdStoragecard());

            return $this->render('history/movedproduct.html.twig', [
                'id' => $id,
                'storageCard' => $storageCard,
                'movedHistory' => $movedHistory
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    #[Route('/history/user/{id}', name: 'tracability_history')]
    public function userHistory(int $id): Response
    {
        try {
            $storageCard = $this->searchService->findStorageCard($id);

            if (!$storageCard) {
                $this->addFlash('error', 'Fiche de stockage non trouvée.');
                return $this->redirectToRoute($this->getRedirectRoute());
            }

            $tracability = $this->historyService->getUserHistory($storageCard->getIdStoragecard());

            return $this->render('history/productuser.html.twig', [
                'id' => $id,
                'storageCard' => $storageCard,
                'tracability' => $tracability
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplate(): string
    {
        // Ce contrôleur n'utilise pas directement le template de recherche
        // mais c'est nécessaire pour l'interface
        return 'history/movedproduct.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectRoute(): string
    {
        return 'home_page';
    }
}