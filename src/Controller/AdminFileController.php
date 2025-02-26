<?php

namespace App\Controller;

use App\Entity\Analysisfile;
use App\Entity\Securityfile;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminFileController extends AbstractController
{
    #[Route('/admin/file', name: 'admin_file')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        //try {
        //Initialise le repository pour la base de données
        $repositoryAnalysisFile = $entityManager->getRepository(Analysisfile::class);
        $repositorySecurityFile = $entityManager->getRepository(Securityfile::class);

        //Cherche tous les fichiers sans fiche de stockage
        $securityFiles = $repositorySecurityFile->findAllByNullSecurityFile();
        $analysisFiles = $repositoryAnalysisFile->findAllByNullAnalysisFile();
        //}
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     //on redirige vers la page d'accueil
        //     return $this->redirectToRoute('home_page');
        // }

        //Quoi qu'il arrive on rend la page initiale avec la liste des fichiers inutiles
        return $this->render('admin/file.html.twig', [
            'securityFiles' => $securityFiles,
            'analysisFiles' => $analysisFiles
        ]);
    }

    #[Route('/admin/file/remove', name: 'admin_file_remove')]
    public function remove(
        Request $request,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager
    ): Response {
        //try {
        $repositoryAnalysisFile = $entityManager->getRepository(Analysisfile::class);
        $repositorySecurityFile = $entityManager->getRepository(Securityfile::class);

        //Cherche tous les fichiers sans fiche de stockage
        $securityFiles = $repositorySecurityFile->findAllByNullSecurityFile();
        $analysisFiles = $repositoryAnalysisFile->findAllByNullAnalysisFile();

        if(!empty($securityFiles) or !empty($analysisFiles)){
            foreach ($securityFiles as $securityFile) {
                $fileUploader->delete(
                    $this->getParameter('idSecurityFile_directory')
                    . "/"
                    . $securityFile->getNameSecurityfile());
                $entityManager->remove($securityFile);
                $entityManager->flush();
            }
            foreach ($analysisFiles as $analysisFile) {
                $fileUploader->delete(
                    $this->getParameter('idAnalysisFile_directory')
                    . "/"
                    . $analysisFile->getNameAnalysisfile());
                $entityManager->remove($analysisFile);
                $entityManager->flush();
            }
            $this->addFlash('success',
                'Les fichiers inutiles ont été supprimé avec succès.');
        }
        else {
            $this->addFlash('error',
                'Il n\'y a aucun fichier à supprimer');
        }


        //}
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //                 'Les fichiers n\'ont pas pu être supprimé.
        //                 Leur chemin n\'existe pas. Merci de vérifier
        //                 l\'intégrité du dossier source.');
        //     return $this->redirectToRoute('admin/file.html.twig', [
        //                     ]);
        // }

        //Quoi qu'il arrive on rend la page initiale avec la liste des fichiers inutiles
        return $this->render('admin/file.html.twig', [
            'securityFiles' => $securityFiles,
            'analysisFiles' => $analysisFiles
        ]);
    }
}