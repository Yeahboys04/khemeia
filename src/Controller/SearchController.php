<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractSearchController
{
    #[Route('/search', name: 'search')]
    public function index(Request $request): Response
    {
        try {
            // Utilise la méthode commune pour traiter la recherche
            $templateVars = $this->handleSearch($request);

            // Rend le template avec les variables
            return $this->render($this->getTemplate(), $templateVars);
        } catch (\Exception $e) {
            // Utilise la méthode commune pour gérer les exceptions
            return $this->handleException($e, $this->getRedirectRoute());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplate(): string
    {
        return 'search/index.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectRoute(): string
    {
        return 'home_page';
    }
}