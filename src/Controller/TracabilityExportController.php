<?php

namespace App\Controller;

use App\Entity\Tracability;
use App\Entity\User;
use App\Form\TracabilityFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TracabilityExportController extends AbstractController
{
    #[Route('/tracability/export', name: 'tracability_export')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $tokenStorage->getToken()->getUser();

        // Vérifier si l'utilisateur est un administrateur
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        // Créer le formulaire de filtrage
        $formData = [
            'defaultFilter' => true,
            'user' => $user,
            'showSymbols' => true,
            'showCautionaryAdvice' => true,
            'showDangerNotes' => true,
            'showProductTypes' => true,
            'showQuantity' => true,
            'showDate' => true,
            'showCMR' => true,
        ];

        $form = $this->createForm(TracabilityFilterType::class, $formData, [
            'entityManager' => $entityManager,
            'is_admin' => $isAdmin,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            // Récupérer la valeur de showAllUsers si elle existe (cas administrateur)
            if ($isAdmin && $form->has('showAllUsers')) {
                $showAllUsers = $form->get('showAllUsers')->getData();
                $formData['showAllUsers'] = $showAllUsers;

                // Si l'option "tous les utilisateurs" est cochée, on définit user à null
                // pour que la requête récupère tous les utilisateurs
                if ($showAllUsers) {
                    $formData['user'] = null;
                }
            }

            // Générer le PDF avec les filtres
            return $this->generatePdf($formData, $entityManager, $tokenStorage);
        }

        return $this->render('tracability/export.html.twig', [
            'form' => $form->createView(),
            'is_admin' => $isAdmin,
        ]);
    }

    /**
     * Génère un PDF basé sur les filtres sélectionnés
     */
    private function generatePdf(array $filterData, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $repository = $entityManager->getRepository(Tracability::class);
        $user = $tokenStorage->getToken()->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        try {
            // Si le filtre par défaut est sélectionné, utiliser l'utilisateur connecté
            if ($filterData['defaultFilter']) {
                $tracabilityData = $repository->findTracabilityByUser($user->getIdUser());
            } else {
                // Sinon, appliquer les filtres personnalisés

                // Pour les administrateurs, utiliser l'utilisateur sélectionné dans le formulaire
                // ou tous les utilisateurs si showAllUsers est coché
                // Pour les utilisateurs standards, forcer l'utilisation de leur propre identifiant
                if (!$isAdmin && (!isset($filterData['user']) || $filterData['user'] !== $user)) {
                    $filterData['user'] = $user; // Forcer l'utilisateur à lui-même si ce n'est pas un admin
                }

                $tracabilityData = $this->getFilteredTracabilityData($filterData, $repository);
            }

            // Configurer les options PDF
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            $pdfOptions->set('isRemoteEnabled', true);
            $pdfOptions->set('isHtml5ParserEnabled', true);
            $pdfOptions->set('tempDir', sys_get_temp_dir());

            $dompdf = new Dompdf($pdfOptions);

            // Obtenir le chemin absolu du répertoire public
            $publicDir = $this->getParameter('kernel.project_dir') . '/public';

            // Générer le HTML
            $html = $this->renderView('tracability/pdf_template.html.twig', [
                'tracability' => $tracabilityData,
                'filters' => $filterData,
                'date' => new \DateTime(),
                'user' => $user,
                'publicDir' => $publicDir,
                'is_admin' => $isAdmin,
                'showAllUsers' => $isAdmin && isset($filterData['showAllUsers']) && $filterData['showAllUsers'],
            ]);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            // Générer le nom du fichier
            $filename = 'historique_produits_' . date('Y-m-d') . '.pdf';

            // Retourner le PDF comme réponse
            $response = new Response($dompdf->output());
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

            return $response;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la génération du PDF: ' . $e->getMessage());
            return $this->redirectToRoute('tracability_export');
        }
    }

    /**
     * Récupère les données de traçabilité filtrées
     */
    private function getFilteredTracabilityData(array $filterData, $repository): array
    {
        // Si l'administrateur a coché l'option "Tous les utilisateurs",
        // nous nous assurons que le paramètre user est bien à null
        if (isset($filterData['showAllUsers']) && $filterData['showAllUsers']) {
            $filterData['user'] = null;
        }

        return $repository->findFilteredTracability($filterData);
    }
}