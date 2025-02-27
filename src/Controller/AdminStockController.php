<?php

namespace App\Controller;

use App\Entity\Cupboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Site;
use App\Entity\Stock;
use App\Form\StockType;

class AdminStockController extends AbstractController
{
    #[Route('/admin/site/stock/{id}', name: 'admin_stock')]
    public function index(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositorySite = $entityManager->getRepository(Site::class);
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositoryCupboard = $entityManager->getRepository(Cupboard::class);

        $site = $repositorySite->find($id);
        $sites = $repositorySite->findAll();

        $firstStock = $repositoryStock->findOneBy([
            'idSite' => $site->getIdSite()]);

        $firstCupboard = $repositoryCupboard->findOneBy([
            'idStock' => $firstStock->getIdStock()]);

        if ($site != null || !empty($site)) {

            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite()]);
            //$stock = $repositoryStock->find($id);
            $stock = new Stock();

            $form = $this->createForm(StockType::class, $stock, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $stock = $form->getData();

                $stock->setIdSite($site);

                $entityManager->persist($stock);
                $entityManager->flush();
                $this->addFlash('success',
                    'L\'entrepôt a été créé avec succès.');

                return $this->redirectToRoute('admin_stock', [
                    'id' => $id,
                ]);
            }
            return $this->render('admin/stock.html.twig', [
                'form' => $form->createView(),
                'action' => 'create',
                'site' => $site,
                'stock' => $stock,
                'sites' => $sites,
                'stocks' => $stocks,
                'firstStock' => $firstStock,
                'firstCupboard' => $firstCupboard,
            ]);
        } else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. Le site '
                . ' N° \' ' . $id . ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }
        // }
        // catch (\LogicException $e){
        //}
        // catch (\Exception $e){
        //     $this->addFlash('error',
        //         'Attention, un site possède déjà ce nom'
        //         .'. Le site n\'a pas pu etre modifié.');
        //         return $this->redirectToRoute('admin_site');
        // }
        return $this->render('admin/stock.html.twig', [
            'id' => $id
        ]);
    }

    #[Route('/admin/site/stock/modify/{id}', name: 'admin_stock_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositorySite = $entityManager->getRepository(Site::class);

        $sites = $repositorySite->findAll();

        $stock = $repositoryStock->find($id);

        if ($stock != null || !empty($stock) ){

            $site = $stock->getIdSite();
            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite() ]);

            $form = $this->createForm(StockType::class, $stock, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $stock = $form->getData();

                $stock->setIdSite($site);

                $entityManager->persist($stock);
                $entityManager->flush();
                $this->addFlash('success',
                    'L\'entrepôt a été modifié avec succès.');

                return $this->redirectToRoute('admin_stock', [
                    'id' => $site->getIdSite(),
                ]);
            }
            return $this->render('admin/stock.html.twig', [
                'form' => $form->createView(),
                'action' => 'modify',
                'site' => $site,
                'sites' => $sites,
                'stock' => $stock,
                'stocks' => $stocks,
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. Le site '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }
        // }
        // catch (\Exception $e){
        //     $this->addFlash('error',
        //         'Attention, un site possède déjà ce nom'
        //         .'. Le site n\'a pas pu etre modifié.');
        //         return $this->redirectToRoute('admin_site');
        // }
        return $this->render('admin/structure.html.twig', [
            'id' => $id,
            'action' => 'modify'
        ]);
    }

    #[Route('/admin/site/stock/remove/{id}', name: 'admin_stock_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositorySite = $entityManager->getRepository(Site::class);

        $sites = $repositorySite->findAll();

        $stock = $repositoryStock->find($id);

        if ($stock != null || !empty($stock) ){

            $site = $stock->getIdSite();
            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite() ]);

            $form = $this->createForm(StockType::class, $stock, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $entityManager->remove($stock);
                $entityManager->flush();
                $this->addFlash('success',
                    'L\'entrepôt a été supprimé avec succès.');

                return $this->redirectToRoute('admin_stock', [
                    'id' => $site->getIdSite(),
                ]);
            }
            return $this->render('admin/stock.html.twig', [
                'form' => $form->createView(),
                'action' => 'remove',
                'site' => $site,
                'sites' => $sites,
                'stock' => $stock,
                'stocks' => $stocks,
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. Le site '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }
        // }
        // catch (\Exception $e){
        //     $this->addFlash('error',
        //         'Attention, un site possède déjà ce nom'
        //         .'. Le site n\'a pas pu etre modifié.');
        //         return $this->redirectToRoute('admin_site');
        // }
        return $this->render('admin/structure.html.twig', [
            'id' => $id,
            'action' => 'remove'
        ]);
    }
}