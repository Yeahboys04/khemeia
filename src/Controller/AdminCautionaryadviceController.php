<?php

namespace App\Controller;

use App\Entity\Cautionaryadvice;
use App\Form\CautionaryadviceType;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class AdminCautionaryadviceController
 */
class AdminCautionaryadviceController extends AbstractController
{
    #[Route('/admin/cautionaryadvice', name: 'admin_cautionaryadvice')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // try {
        $repositoryCautionaryadvice = $entityManager->getRepository(Cautionaryadvice::class);

        $cautionaryadvices = $repositoryCautionaryadvice->findAll();
        $cautionaryadvice = new Cautionaryadvice();

        $form = $this->createForm(CautionaryadviceType::class, $cautionaryadvice, [
            'method' => 'POST',]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cautionaryadvice = $form->getData();

            $entityManager->persist($cautionaryadvice);
            $entityManager->flush();

            $this->addFlash('success',
                'Le conseil de prudence a été créé avec succès.');
            return $this->redirectToRoute('admin_cautionaryadvice');
        }
        // }
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     return $this->redirectToRoute('home_page');
        // }

        return $this->render('admin/cautionaryadvice.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'cautionaryadvices' => $cautionaryadvices,
        ]);
    }

    /**
     * Modifier un conseil de prudence via un formulaire
     */
    #[Route('/admin/cautionaryadvice/modify/{id}', name: 'admin_cautionaryadvice_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            //Initialise le repositoryCautionaryadvice pour la base de données
            $repositoryCautionaryadvice = $entityManager->getRepository(Cautionaryadvice::class);

            //Cherche tous les conseils de prudence
            $cautionaryadvices = $repositoryCautionaryadvice->findAll();

            $previousCautionaryadvice = $repositoryCautionaryadvice->find($id);

            if ($previousCautionaryadvice != null || !empty($previousCautionaryadvice) ){

                $form = $this->createForm(CautionaryadviceType::class, $previousCautionaryadvice, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $cautionaryadvice = $form->getData();

                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le conseil de prudence a été modifié avec succès.');

                    return $this->redirectToRoute('admin_cautionaryadvice');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le conseil de prudence '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_cautionaryadvice');
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

        return $this->render('admin/cautionaryadvice.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'cautionaryadvices' => $cautionaryadvices,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un conseils de prudence
     */
    #[Route('/admin/cautionaryadvice/remove/{id}', name: 'admin_cautionaryadvice_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repositoryCautionaryadvice = $entityManager->getRepository(Cautionaryadvice::class);

            $cautionaryadvices = $repositoryCautionaryadvice->findAll();
            $cautionaryadvice = $repositoryCautionaryadvice->find($id);

            if ($cautionaryadvice != null || !empty($cautionaryadvice) ){

                $form = $this->createForm(CautionaryadviceType::class, $cautionaryadvice, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($cautionaryadvice);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le conseil de prudence a été supprimé avec succès.');
                    return $this->redirectToRoute('admin_cautionaryadvice');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le conseil de prudence'
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_cautionaryadvice');
            }
        }

        catch (ForeignKeyConstraintViolationException $fkcve){
            $this->addFlash('error', 'Attention, le conseil de prudence '
                .'n\'a pas pu être supprimé car il est relié à produit'
                .'.');
            return $this->redirectToRoute('home_page');
        }
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/cautionaryadvice.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'cautionaryadvices' => $cautionaryadvices,
            'id' => $id
        ]);
    }
}