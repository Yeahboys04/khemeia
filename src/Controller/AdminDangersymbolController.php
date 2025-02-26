<?php

namespace App\Controller;

use App\Entity\Dangersymbol;
use App\Form\DangersymbolType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class AdminDangersymbolController extends AbstractController
{
    #[Route('/admin/dangersymbol', name: 'admin_dangersymbol')]
    public function index(Request $request, FileUploader $fileUploader, EntityManagerInterface $entityManager): Response
    {
        try {
            $repositoryDangersymbol = $entityManager->getRepository(Dangersymbol::class);

            $dangersymbols = $repositoryDangersymbol->findAll();
            $dangersymbol = new Dangersymbol();

            $form = $this->createForm(DangersymbolType::class, $dangersymbol, [
                'method' => 'POST',]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $dangersymbol = $form->getData();

                $iconFile = $form->get('uploadedFile')->getData();

                //cette condition est necessaire car le champ "Icone" n'est
                //pas obligatoire
                if ($iconFile != null) {
                    $newFileName =
                        $fileUploader->upload(
                            $iconFile,
                            $this->getParameter('icon_directory'));

                    $dangersymbol->setIcon($newFileName);
                }

                $entityManager->persist($dangersymbol);
                $entityManager->flush();

                $this->addFlash('success',
                    'Le conseil de prudence a été créé avec succès.');
                return $this->redirectToRoute('admin_dangersymbol');
            }
        } catch (FileException $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue lors du déplacement du fichier.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }
            // S'il y a tout autre exception
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/dangersymbol.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'dangersymbols' => $dangersymbols,
        ]);
    }

    /**
     * Modifier un conseil de prudence via un formulaire
     */
    #[Route('/admin/dangersymbol/modify/{id}', name: 'admin_dangersymbol_modify')]
    public function modify(
        Request $request,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager,
        $id
    ): Response {
        try {
            //Initialise le repositoryDangersymbol pour la base de données
            $repositoryDangersymbol = $entityManager->getRepository(Dangersymbol::class);

            //Cherche tous les conseils de prudence
            $dangersymbols = $repositoryDangersymbol->findAll();

            $previousDangersymbol = $repositoryDangersymbol->find($id);
            $previousIcon = $previousDangersymbol->getIcon();
            $previousIconFile = new File($this->getParameter('icon_directory').'/'.$previousIcon);


            if ($previousDangersymbol != null || !empty($previousDangersymbol) ){

                $form = $this->createForm(DangersymbolType::class, $previousDangersymbol, [
                    'method' => 'POST',
                ]);

                $form->get('uploadedFile')->setData($previousIconFile);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $dangersymbol = $form->getData();

                    $iconFile = $form->get('uploadedFile')->getData();

                    //cette condition est necessaire car le champ "Icone" n'est
                    //pas obligatoire
                    if ($iconFile != null) {
                        $newFileName =
                            $fileUploader->upload(
                                $iconFile,
                                $this->getParameter('icon_directory'));

                        $dangersymbol->setIcon($newFileName);
                    }

                    $entityManager->persist($dangersymbol);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le conseil de prudence a été modifié avec succès.');

                    return $this->redirectToRoute('admin_dangersymbol');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le conseil de prudence '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_dangersymbol');
            }
        }
        catch (FileException $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue lors du déplacement du fichier.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }
            // S'il y a tout autre exception
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/dangersymbol.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'dangersymbols' => $dangersymbols,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un conseils de prudence
     */
    #[Route('/admin/dangersymbol/remove/{id}', name: 'admin_dangersymbol_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repositoryDangersymbol = $entityManager->getRepository(Dangersymbol::class);

            $dangersymbols = $repositoryDangersymbol->findAll();
            $dangersymbol = $repositoryDangersymbol->find($id);

            if ($dangersymbol != null || !empty($dangersymbol) ){

                $form = $this->createForm(DangersymbolType::class, $dangersymbol, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($dangersymbol);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le conseil de prudence a été supprimé avec succès.');
                    return $this->redirectToRoute('admin_dangersymbol');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le conseil de prudence'
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_dangersymbol');
            }
        }

        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/dangersymbol.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'dangersymbols' => $dangersymbols,
            'id' => $id
        ]);
    }
}