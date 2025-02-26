<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Form\SupplierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSupplierController extends AbstractController
{
    #[Route('/admin/supplier', name: 'admin_supplier')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // try {
        $repositorySupplier = $entityManager->getRepository(Supplier::class);

        $suppliers = $repositorySupplier->findAll();
        $supplier = new Supplier();

        $form = $this->createForm(SupplierType::class, $supplier, [
            'method' => 'POST',]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supplier = $form->getData();

            $entityManager->persist($supplier);
            $entityManager->flush();

            $this->addFlash('success',
                'Le fournisseur a été créé avec succès.');
            return $this->redirectToRoute('admin_supplier');
        }
        // }
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     return $this->redirectToRoute('home_page');
        // }

        return $this->render('admin/supplier.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Modifier un fournisseur via un formulaire
     */
    #[Route('/admin/supplier/modify/{id}', name: 'admin_supplier_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            //Initialise le repositorySupplier pour la base de données
            $repositorySupplier = $entityManager->getRepository(Supplier::class);

            //Cherche tous les fournisseurs
            $suppliers = $repositorySupplier->findAll();

            $previousSupplier = $repositorySupplier->find($id);

            if ($previousSupplier != null || !empty($previousSupplier) ){

                $form = $this->createForm(SupplierType::class, $previousSupplier, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $supplier = $form->getData();

                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le fournisseur a été modifié avec succès.');

                    return $this->redirectToRoute('admin_supplier');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le fournisseur '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_supplier');
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

        return $this->render('admin/supplier.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'suppliers' => $suppliers,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un fournisseur
     */
    #[Route('/admin/supplier/remove/{id}', name: 'admin_supplier_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repositorySupplier = $entityManager->getRepository(Supplier::class);

            $suppliers = $repositorySupplier->findAll();
            $supplier = $repositorySupplier->find($id);

            if ($supplier != null || !empty($supplier) ){

                $form = $this->createForm(SupplierType::class, $supplier, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($supplier);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le fournisseur a été supprimé avec succès.');
                    return $this->redirectToRoute('admin_supplier');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le fournisseur '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_supplier');
            }
        }

        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/supplier.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'suppliers' => $suppliers,
            'id' => $id
        ]);
    }
}