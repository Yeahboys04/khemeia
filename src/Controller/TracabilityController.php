<?php

namespace App\Controller;

use App\Entity\Tracability;
use App\Service\SearchService;
use App\Service\TracabilityService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TracabilityController extends AbstractSearchController
{
    public function __construct(
        private readonly TracabilityService $tracabilityService,
        private readonly TokenStorageInterface $tokenStorage,
        SearchService $searchService
    ) {
        parent::__construct($searchService);
    }

    #[Route('/tracability', name: 'tracability')]
    public function index(): Response
    {
        try {
            $user = $this->getUser();
            $tracability = $this->tracabilityService->getUserTracability($user->getIdUser());

            return $this->render('tracability/index.html.twig', [
                'tracability' => $tracability,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    #[Route('/tracability/cart/{id}', name: 'tracability_add')]
    public function addProduct(int $id): Response
    {
        try {
            $user = $this->getUser();
            $storagecard = $this->searchService->findStorageCard($id);

            if (!$storagecard) {
                $this->addFlash('error', 'Fiche de stockage non trouvée.');
                return $this->redirectToRoute('tracability_search');
            }

            $this->tracabilityService->addProductToTracability($storagecard, $user);

            $this->addFlash('success', 'Le produit a bien été ajouté à votre historique.');
            return $this->redirectToRoute('tracability_search');
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    #[Route('/tracability/search', name: 'tracability_search')]
    public function search(Request $request): Response
    {
        try {
            $templateVars = $this->handleSearch($request);
            return $this->render($this->getTemplate(), $templateVars);
        } catch (\Exception $e) {
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplate(): string
    {
        return 'tracability/cart.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectRoute(): string
    {
        return 'home_page';
    }
}