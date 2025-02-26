<?php

namespace App\Controller;

use App\Entity\Dangernote;
use App\Form\DangernoteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminDangernoteController extends AbstractController
{
    #[Route('/admin/dangernote', name: 'admin_dangernote')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // try {
        $repositoryDangernote = $entityManager->getRepository(Dangernote::class);

        $dangernotes = $repositoryDangernote->findAll();
        $dangernote = new Dangernote();

        $form = $this->createForm(DangernoteType::class, $dangernote, [
            'method' => 'POST',]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dangernote = $form->getData();

            $entityManager->persist($dangernote);
            $entityManager->flush();

            $this->addFlash('success',
                'La mention de danger a été créé avec succès.');
            return $this->redirectToRoute('admin_dangernote');
        }
        // }
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     return $this->redirectToRoute('home_page');
        // }

        return $this->render('admin/dangernote.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'dangernotes' => $dangernotes,
        ]);
    }

    /**
     * Modifier une mention de danger via un formulaire
     */
    #[Route('/admin/dangernote/modify/{id}', name: 'admin_dangernote_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            //Initialise le repositoryDangernote pour la base de données
            $repositoryDangernote = $entityManager->getRepository(Dangernote::class);

            //Cherche tous les conseils de prudence
            $dangernotes = $repositoryDangernote->findAll();

            $previousDangernote = $repositoryDangernote->find($id);

            if ($previousDangernote != null || !empty($previousDangernote) ){

                $form = $this->createForm(DangernoteType::class, $previousDangernote, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $dangernote = $form->getData();

                    $entityManager->flush();
                    $this->addFlash('success',
                        'La mention de danger a été modifié avec succès.');

                    return $this->redirectToRoute('admin_dangernote');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. La mention de danger '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_dangernote');
            }
        }
            // S'il y a tout autre exception
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/dangernote.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'dangernotes' => $dangernotes,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un conseils de prudence
     */
    #[Route('/admin/dangernote/remove/{id}', name: 'admin_dangernote_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repositoryDangernote = $entityManager->getRepository(Dangernote::class);

            $dangernotes = $repositoryDangernote->findAll();
            $dangernote = $repositoryDangernote->find($id);

            if ($dangernote != null || !empty($dangernote) ){

                $form = $this->createForm(DangernoteType::class, $dangernote, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($dangernote);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'La mention de danger a été supprimé avec succès.');
                    return $this->redirectToRoute('admin_dangernote');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. La mention de danger'
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_dangernote');
            }
        }

        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/dangernote.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'dangernotes' => $dangernotes,
            'id' => $id
        ]);
    }
}