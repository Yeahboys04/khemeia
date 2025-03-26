<?php

namespace App\Controller;

use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur abstrait fournissant des fonctionnalités communes pour les contrôleurs de recherche
 */
abstract class AbstractSearchController extends AbstractController
{
    public function __construct(
        protected readonly SearchService $searchService
    ) {
    }

    /**
     * Traite une recherche et retourne les résultats formatés pour le template
     */
    protected function handleSearch(Request $request, array $formOptions = []): array
    {
        $form = $this->searchService->createSearchForm($formOptions);
        $results = $this->searchService->processSearchForm($form, $request);

        $templateVars = [
            'form' => $form->createView(),
            'search_submitted' => $results['search_submitted']
        ];

        if ($results['storagecards'] !== null) {
            $templateVars['storagecards'] = $results['storagecards'];
            $templateVars['site'] = $results['site'];

            if ($results['searchAll'] !== null) {
                $templateVars['searchAll'] = $results['searchAll'];
            }
        }

        if ($results['error'] !== null) {
            $this->addFlash('error', $results['error']);
        }

        return $templateVars;
    }

    /**
     * Gère les exceptions et retourne une réponse appropriée
     */
    protected function handleException(\Exception $e, string $redirectRoute): Response
    {
        throw $e;
        $this->addFlash('error', 'Attention, une erreur est survenue. Contactez votre administrateur.');
        return $this->redirectToRoute($redirectRoute);
    }

    /**
     * Les contrôleurs enfants doivent implémenter cette méthode pour définir le template à utiliser
     */
    abstract protected function getTemplate(): string;

    /**
     * Les contrôleurs enfants doivent implémenter cette méthode pour définir la route de redirection en cas d'erreur
     */
    abstract protected function getRedirectRoute(): string;
}