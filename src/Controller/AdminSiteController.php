<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\SiteType;
use App\Entity\Site;

class AdminSiteController extends AbstractController
{
    #[Route('/admin/site', name: 'admin_site')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            //Initialise le repository pour la base de données
            $repository = $entityManager->getRepository(Site::class);

            //Cherche tous les sites
            $sites = $repository->findAll();

            //Initialise le formulaire
            $site = new Site();

            $form = $this->createForm(SiteType::class, $site, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            //Si le formulaire est soumis est valide
            if ($form->isSubmitted() && $form->isValid()) {
                //on récupère les données
                $site = $form->getData();

                //On passe le site à la base de données grâce à doctrine
                $entityManager->persist($site);
                $entityManager->flush();
                //On enregistre un message flash
                $this->addFlash('success',
                    'Le site a été créé avec succès.');

                //On renvoi la page initiale
                return $this->redirectToRoute('admin_site');
            }
        }
            //S'il y a une exception de clé dupliquée
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                'Attention, un site possède déjà ce nom'
                .'. Le site n\'a pas pu etre créé.');
        }
            //S'il y a tout autre exception
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }

        //Quoi qu'il arrive on rend la page initiale
        return $this->render('admin/site.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'sites' => $sites,
        ]);
    }

    /**
     * Modifier un site via un formulaire
     */
    #[Route('/admin/site/modify/{id}', name: 'admin_site_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            //Initialise le repository pour la base de données
            $repository = $entityManager->getRepository(Site::class);

            //Cherche tous les sites
            $sites = $repository->findAll();

            $previousSite = $repository->find($id);

            if ($previousSite != null || !empty($previousSite) ){

                $form = $this->createForm(SiteType::class, $previousSite, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $site = $form->getData();

                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le site a été modifié avec succès.');

                    return $this->redirectToRoute('admin_site');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le site '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_user');
            }
        }
            //S'il y a une exception de clé dupliquée
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                'Attention, un site possède déjà ce nom'
                .'. Le site n\'a pas pu etre modifié.');
        }

        return $this->render('admin/site.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'sites' => $sites,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un site
     */
    #[Route('/admin/site/remove/{id}', name: 'admin_site_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repository = $entityManager->getRepository(Site::class);

            $sites = $repository->findAll();
            $site = $repository->find($id);

            if ($site != null || !empty($site) ){

                $form = $this->createForm(SiteType::class, $site, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($site);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le site a été supprimé avec succès.');
                    return $this->redirectToRoute('admin_site');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le site '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_user');
            }
        }

        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/site.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'sites' => $sites,
            'id' => $id
        ]);
    }
}