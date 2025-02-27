<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Site;
use App\Entity\Stock;
use App\Entity\Cupboard;
use App\Form\CupboardType;

class AdminCupboardController extends AbstractController
{
    #[Route('/admin/stock/cupboard/{id}', name: 'admin_cupboard')]
    public function index(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositorySite = $entityManager->getRepository(Site::class);
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositoryCupboard = $entityManager->getRepository(Cupboard::class);

        $stock = $repositoryStock->find($id);
        $site = $stock->getIdSite();

        $sites = $repositorySite->findAll();
        $stocks = $repositoryStock->findBy([
            'idSite' => $site->getIdSite()]);

        $firstCupboard = $repositoryCupboard->findOneBy([
            'idStock' => $stock->getIdStock()]);

        if ($stock != null || !empty($stock) ){

            $cupboards = $repositoryCupboard->findBy([
                'idStock' => $stock->getIdStock()]);

            $cupboard = new Cupboard();
            $cupboard->setIdStock($stock);

            $form = $this->createForm(CupboardType::class, $cupboard, [
                'method' => 'POST',
                'idStock'=> $stock->getIdStock()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $cupboard = $form->getData();

                $entityManager->persist($cupboard);
                $entityManager->flush();
                $this->addFlash('success',
                    'L\'armoire a été créée avec succès.');

                return $this->redirectToRoute('admin_cupboard', [
                    'id' => $stock->getIdStock()
                ]);
            }
            return $this->render('admin/cupboard.html.twig', [
                'form' => $form->createView(),
                'action' => 'create',
                'stock' => $stock,
                'sites' => $sites,
                'stocks' => $stocks,
                'cupboards' => $cupboards,
                'site' => $site,
                'firstCupboard' => $firstCupboard
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. L\'armoire '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_stock');
        }
        // }
        // catch (\Exception $e){
        //     $this->addFlash('error',
        //         'Attention, un stock possède déjà ce nom'
        //         .'. Le stock n\'a pas pu etre modifié.');
        //         return $this->redirectToRoute('admin_stock');
        // }
        return $this->render('admin/cupboard.html.twig', [
            'id' => $id,
            'site' => $site,
            'action' => 'create'
        ]);
    }

    #[Route('/admin/stock/cupboard/modify/{id}', name: 'admin_cupboard_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositoryCupboard = $entityManager->getRepository(Cupboard::class);
        $repositorySite = $entityManager->getRepository(Site::class);
        $repositoryStock = $entityManager->getRepository(Stock::class);

        $cupboard = $repositoryCupboard->find($id);

        $sites = $repositorySite->findAll();

        if ($cupboard != null || !empty($cupboard) ){

            $stock = $cupboard->getIdStock();
            $site = $stock->getIdSite();

            $cupboards = $repositoryCupboard->findBy([
                'idStock' => $stock->getIdStock()]);
            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite()]);

            $form = $this->createForm(CupboardType::class, $cupboard, [
                'method' => 'POST',
                'idStock'=> $stock->getIdStock()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $cupboard = $form->getData();

                $entityManager->flush();
                $this->addFlash('success',
                    'L\'armoire a été modifiée avec succès.');

                return $this->redirectToRoute('admin_cupboard', [
                    'id' => $stock->getIdStock()
                ]);
            }
            return $this->render('admin/cupboard.html.twig', [
                'form' => $form->createView(),
                'action' => 'modify',
                'stock' => $stock,
                'cupboard'=>$cupboard,
                'sites' => $sites,
                'stocks' => $stocks,
                'cupboards' => $cupboards,
                'site' => $site
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. L\'armoire '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }
        // }
        // catch (\Exception $e){
        //     $this->addFlash('error',
        //         'Attention, un stock possède déjà ce nom'
        //         .'. Le stock n\'a pas pu etre modifié.');
        //         return $this->redirectToRoute('admin_stock');
        // }
        return $this->render('admin/cupboard.html.twig', [
            'id' => $id,
            'action' => 'modify'
        ]);
    }

    #[Route('/admin/stock/cupboard/remove/{id}', name: 'admin_cupboard_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositorySite = $entityManager->getRepository(Site::class);
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositoryCupboard = $entityManager->getRepository(Cupboard::class);

        $cupboard = $repositoryCupboard->find($id);
        $sites = $repositorySite->findAll();

        if ($cupboard != null || !empty($cupboard) ){

            $stock = $cupboard->getIdStock();
            $site = $stock->getIdSite();
            $cupboards = $repositoryCupboard->findBy([
                'idStock' => $stock->getIdStock()]);
            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite()]);

            $form = $this->createForm(CupboardType::class, $cupboard, [
                'method' => 'POST',
                'idStock'=> $stock->getIdStock()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $entityManager->remove($cupboard);
                $entityManager->flush();
                $this->addFlash('success',
                    'L\'armoire a été suprimée avec succès.');

                return $this->redirectToRoute('admin_cupboard', [
                    'id' => $stock->getIdStock()
                ]);
            }
            return $this->render('admin/cupboard.html.twig', [
                'form' => $form->createView(),
                'action' => 'remove',
                'stock' => $stock,
                'cupboard'=>$cupboard,
                'sites' => $sites,
                'stocks' => $stocks,
                'cupboards' => $cupboards,
                'site' => $site
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. L\'armoire '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }
        // }
        // catch (\Exception $e){
        //     $this->addFlash('error',
        //         'Attention, un stock possède déjà ce nom'
        //         .'. Le stock n\'a pas pu etre modifié.');
        //         return $this->redirectToRoute('admin_stock');
        // }
        return $this->render('admin/cupboard.html.twig', [
            'id' => $id,
            'action' => 'remove'
        ]);
    }
}