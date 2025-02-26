<?php

namespace App\Controller;

use App\Entity\Chimicalproduct;
use App\Entity\Document;
use App\Form\ChimicalproductType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileController extends AbstractController
{
    #[Route('/admin/reference/product', name: 'fileController')]
    public function index(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        try {
            //Initialise le repository pour la base de données
            $repositoryProduct = $entityManager->getRepository(Chimicalproduct::class);

            //Cherche tous les produits
            $products = $repositoryProduct->findAll();

            //Initialise le formulaire
            $product = new Chimicalproduct();

            $form = $this->createForm(ChimicalproductType::class, $product, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            //Si le formulaire est soumis et valide
            if ($form->isSubmitted()) {
                //on récupère les données
                $product = $form->getData();

                $documentFile = $form->get('idDocument')->getData();

                //cette condition est necessaire car le champ "Fiche de prudence" n'est
                //pas obligatoire
                if ($documentFile) {
                    $originalFileName = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);
                    //Ceci est requis pour inclure sécuritairement le nom du fichier comme une
                    //partie de l'url
                    $safeFileName = $slugger->slug($originalFileName);
                    $newFileName = $safeFileName.'-'.uniqid().'.'.$documentFile->guessExtension();
                    //on met à jour le fichier dans produit
                    $document = new Document();
                    $document->setNameDocument($newFileName);
                    $entityManager->persist($document);
                    $entityManager->flush();
                    $product->setIdDocument($document);

                    try {
                        $documentFile->move(
                            $this->getParameter('idSecurityFile_directory'),
                            $newFileName
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error',
                            'Attention, une erreur est survenue lors du déplacement du fichier.'
                            .' Contactez votre administrateur.');
                        //on redirige vers la page d'accueil
                        return $this->redirectToRoute('home_page');
                    }
                }

                //On passe le produit à la base de données grâce à doctrine
                $entityManager->persist($product);
                $entityManager->flush();
                //On enregistre un message flash
                $this->addFlash('success',
                    'Le produit a été créé avec succès.');

                //On renvoi la page initiale
                return $this->redirectToRoute('admin_product');
            }
        }
            //S'il y a une exception de clé dupliquée
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                'Attention, un produit possède déjà ce nom'
                .'. Le product n\'a pas pu etre créé.');
        }
        //S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     //on redirige vers la page d'accueil
        //     return $this->redirectToRoute('home_page');
        // }

        //Quoi qu'il arrive on rend la page initiale
        return $this->render('admin/product.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'products' => $products,
        ]);
    }

    /**
     * Modifier un product via un formulaire
     */
    #[Route('/admin/product/modify/{id}', name: 'fileController_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            //Initialise le repositoryProduct pour la base de données
            $repositoryProduct = $entityManager->getRepository(Chimicalproduct::class);

            //Cherche tous les products
            $products = $repositoryProduct->findAll();

            $previousChimicalproduct = $repositoryProduct->find($id);

            if ($previousChimicalproduct != null || !empty($previousChimicalproduct) ){

                $form = $this->createForm(ChimicalproductType::class, $previousChimicalproduct, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $product = $form->getData();

                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le product a été modifié avec succès.');

                    return $this->redirectToRoute('admin_product');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le product '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_user');
            }
        }
            //S'il y a une exception de clé dupliquée
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                'Attention, un product possède déjà ce nom'
                .'. Le product n\'a pas pu etre modifié.');
        }

        return $this->render('admin/product.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'products' => $products,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un product
     */
    #[Route('/admin/product/remove/{id}', name: 'fileController_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repositoryProduct = $entityManager->getRepository(Chimicalproduct::class);

            $products = $repositoryProduct->findAll();
            $product = $repositoryProduct->find($id);

            if ($product != null || !empty($product) ){

                $form = $this->createForm(ChimicalproductType::class, $product, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($product);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le product a été supprimé avec succès.');
                    return $this->redirectToRoute('admin_product');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le product '
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

        return $this->render('admin/product.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'products' => $products,
            'id' => $id
        ]);
    }
}