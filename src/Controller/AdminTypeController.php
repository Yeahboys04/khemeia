<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTypeController extends AbstractController
{
    #[Route('/admin/type', name: 'admin_type')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // try {
        $repositoryType = $entityManager->getRepository(Type::class);

        $types = $repositoryType->findAll();
        $type = new Type();

        $form = $this->createForm(TypeType::class, $type, [
            'method' => 'POST',]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->getData();

            $entityManager->persist($type);
            $entityManager->flush();

            $this->addFlash('success',
                'Le type de produit a été créé avec succès.');
            return $this->redirectToRoute('admin_type');
        }
        // }
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     return $this->redirectToRoute('home_page');
        // }

        return $this->render('admin/type.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'types' => $types,
        ]);
    }

    /**
     * Modifier un type de produit via un formulaire
     */
    #[Route('/admin/type/modify/{id}', name: 'admin_type_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            //Initialise le repositoryType pour la base de données
            $repositoryType = $entityManager->getRepository(Type::class);

            //Cherche tous les types de produit
            $types = $repositoryType->findAll();

            $previousType = $repositoryType->find($id);

            if ($previousType != null || !empty($previousType) ){

                $form = $this->createForm(TypeType::class, $previousType, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $type = $form->getData();

                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le type de produit a été modifié avec succès.');

                    return $this->redirectToRoute('admin_type');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le type de produit '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_type');
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

        return $this->render('admin/type.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'types' => $types,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un type
     */
    #[Route('/admin/type/remove/{id}', name: 'admin_type_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repositoryType = $entityManager->getRepository(Type::class);

            $types = $repositoryType->findAll();
            $type = $repositoryType->find($id);

            if ($type != null || !empty($type) ){

                $form = $this->createForm(TypeType::class, $type, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($type);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le type de produit a été supprimé avec succès.');
                    return $this->redirectToRoute('admin_type');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le type de produit'
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_type');
            }
        }

        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/type.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'types' => $types,
            'id' => $id
        ]);
    }
}