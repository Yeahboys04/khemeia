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
use App\Entity\Shelvingunit;
use App\Form\ShelvingunitType;

class AdminShelvingunitController extends AbstractController
{
    #[Route('/admin/stock/shelvingunit/{id}', name: 'admin_shelvingunit')]
    public function index(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositorySite = $entityManager->getRepository(Site::class);
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositoryCupboard = $entityManager->getRepository(Cupboard::class);
        $repositoryShelvingunit = $entityManager->getRepository(Shelvingunit::class);

        $cupboard = $repositoryCupboard->find($id);

        $sites = $repositorySite->findAll();

        if ($cupboard != null || !empty($cupboard) ){
            $stock = $cupboard->getIdStock();
            $site = $stock->getIdSite();
            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite()]);
            $cupboards = $repositoryCupboard->findBy([
                'idStock' => $stock->getIdStock()]);

            $shelvingunits = $repositoryShelvingunit->findBy([
                'idCupboard' => $cupboard->getIdCupboard()]);

            $shelvingunit = new Shelvingunit();
            $shelvingunit->setIdCupboard($cupboard);

            $form = $this->createForm(ShelvingunitType::class, $shelvingunit, [
                'method' => 'POST',
                'idCupboard'=> $cupboard->getIdCupboard()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $shelvingunit = $form->getData();

                $entityManager->persist($shelvingunit);
                $entityManager->flush();
                $this->addFlash('success',
                    'L\'étagère a été créée avec succès.');

                return $this->redirectToRoute('admin_shelvingunit', [
                    'id' => $cupboard->getIdCupboard()
                ]);
            }
            return $this->render('admin/shelvingunit.html.twig', [
                'form' => $form->createView(),
                'action' => 'create',
                'site' => $site,
                'stock' => $stock,
                'cupboard' => $cupboard,
                'shelvingunit' =>$shelvingunit,
                'sites' => $sites,
                'stocks' => $stocks,
                'cupboards' => $cupboards,
                'shelvingunits' => $shelvingunits,
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. L\'armoire '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }

        //}
        return $this->render('admin/shelvingunit.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/admin/stock/shelvingunit/modify/{id}', name: 'admin_shelvingunit_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositorySite = $entityManager->getRepository(Site::class);
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositoryCupboard = $entityManager->getRepository(Cupboard::class);
        $repositoryShelvingunit = $entityManager->getRepository(Shelvingunit::class);

        $shelvingunit = $repositoryShelvingunit->find($id);

        if ($shelvingunit != null || !empty($shelvingunit) ){

            $cupboard = $shelvingunit->getIdCupboard();
            $stock = $cupboard->getIdStock();
            $site = $stock->getIdSite();

            $sites = $repositorySite->findAll();
            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite()]);
            $cupboards = $repositoryCupboard->findBy([
                'idStock' => $stock->getIdStock()]);
            $shelvingunits = $repositoryShelvingunit->findBy([
                'idCupboard' => $cupboard->getIdCupboard()]);

            $form = $this->createForm(ShelvingunitType::class, $shelvingunit, [
                'method' => 'POST',
                'idCupboard'=> $cupboard->getIdCupboard()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $shelvingunit = $form->getData();

                $entityManager->flush();
                $this->addFlash('success',
                    'L\'étagère a été modifiée avec succès.');

                return $this->redirectToRoute('admin_shelvingunit', [
                    'id' => $cupboard->getIdCupboard()
                ]);
            }
            return $this->render('admin/shelvingunit.html.twig', [
                'form' => $form->createView(),
                'action' => 'modify',
                'site' => $site,
                'stock' => $stock,
                'cupboard' => $cupboard,
                'shelvingunit' =>$shelvingunit,
                'sites' => $sites,
                'stocks' => $stocks,
                'cupboards' => $cupboards,
                'shelvingunits' => $shelvingunits,
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. L\'armoire '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }

        //}
        return $this->render('admin/shelvingunit.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/admin/stock/shelvingunit/remove/{id}', name: 'admin_shelvingunit_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // try {
        $repositorySite = $entityManager->getRepository(Site::class);
        $repositoryStock = $entityManager->getRepository(Stock::class);
        $repositoryCupboard = $entityManager->getRepository(Cupboard::class);
        $repositoryShelvingunit = $entityManager->getRepository(Shelvingunit::class);

        $shelvingunit = $repositoryShelvingunit->find($id);

        if ($shelvingunit != null || !empty($shelvingunit) ){

            $cupboard = $shelvingunit->getIdCupboard();
            $stock = $cupboard->getIdStock();
            $site = $stock->getIdSite();

            $sites = $repositorySite->findAll();
            $stocks = $repositoryStock->findBy([
                'idSite' => $site->getIdSite()]);
            $cupboards = $repositoryCupboard->findBy([
                'idStock' => $stock->getIdStock()]);
            $shelvingunits = $repositoryShelvingunit->findBy([
                'idCupboard' => $cupboard->getIdCupboard()]);

            $form = $this->createForm(ShelvingunitType::class, $shelvingunit, [
                'method' => 'POST',
                'idCupboard'=> $cupboard->getIdCupboard()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $entityManager->remove($shelvingunit);
                $entityManager->flush();
                $this->addFlash('success',
                    'L\'étagère a été supprimée avec succès.');

                return $this->redirectToRoute('admin_shelvingunit', [
                    'id' => $cupboard->getIdCupboard()
                ]);
            }
            return $this->render('admin/shelvingunit.html.twig', [
                'form' => $form->createView(),
                'action' => 'remove',
                'site' => $site,
                'stock' => $stock,
                'cupboard' => $cupboard,
                'shelvingunit' =>$shelvingunit,
                'sites' => $sites,
                'stocks' => $stocks,
                'cupboards' => $cupboards,
                'shelvingunits' => $shelvingunits,
            ]);
        }
        else {
            $this->addFlash('error',
                'Attention, une erreur est survenue. L\'armoire '
                .' N° \' ' .$id. ' \' n\'existe pas.');

            return $this->redirectToRoute('admin_site');
        }

        //}
        return $this->render('admin/shelvingunit.html.twig', [
            'id' => $id,
        ]);
    }
}