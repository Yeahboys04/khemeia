<?php

namespace App\Controller;

use App\Entity\Analysisfile;
use App\Entity\Chimicalproduct;
use App\Entity\Movedhistory;
use App\Entity\Securityfile;
use App\Entity\Storagecard;
use App\Form\ChimicalproductType;
use App\Form\StoragecardRespType;
use App\Service\FileUploader;
use App\Service\Utility;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'inventory')]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        //try {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        $site = $tokenStorage->getToken()->getUser()->getIdSite()->getIdSite();
        $storagecards = $repositoryStoragecard->loadStorageCardsBySite($site);
        $emptyStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndEmptyStock($site);
        $expirationStoragecards = $repositoryStoragecard->loadStorageCardsBySiteAndExpirationDate($site);
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/index.html.twig', [
            'storagecards' => $storagecards,
            'emptyStoragecards' =>$emptyStoragecards,
            'expirationStoragecards' => $expirationStoragecards,
            'site' => $tokenStorage->getToken()->getUser()->getIdSite()
        ]);
        //}
        // S'il y a tout autre exception
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     //on redirige vers la page d'accueil
        //     return $this->redirectToRoute('home_page');
        // }
    }

    #[Route('/inventory/product', name: 'inventory_product')]
    public function createProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            //Initialise le repository pour la base de données
            $repositoryProduct = $entityManager->getRepository(Chimicalproduct::class);

            //Initialise le formulaire
            $product = new Chimicalproduct();

            $form = $this->createForm(ChimicalproductType::class, $product, [
                'method' => 'POST',
            ]);

            $form->handleRequest($request);
            //Si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                //on récupère les données
                $product = $form->getData();

                //On passe le produit à la base de données grâce à doctrine
                $entityManager->persist($product);
                $entityManager->flush();
                //On enregistre un message flash
                $this->addFlash('success',
                    'Le produit numéro ' . $product->getIdChimicalproduct() . ' a été créé avec succès.');

                //On renvoi la page initiale
                return $this->redirectToRoute('inventory_product');
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

        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/product.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/api/search/products', name: 'api_search_products', methods: ['GET'])]
    public function searchProducts(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer le terme de recherche
        $term = $request->query->get('q', '');

        if (empty($term) || strlen($term) < 2) {
            return new JsonResponse([]);
        }

        // Rechercher dans la base de données
        $repository = $entityManager->getRepository(Chimicalproduct::class);
        $products = $repository->createQueryBuilder('p')
            ->where('p.nameChimicalproduct LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.nameChimicalproduct', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Formatter les résultats
        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->getIdChimicalproduct(),
                'text' => $product->getNameChimicalproduct(),
                'formula' => $product->getFormula(),
                'casnumber' => $product->getCasnumber(),
                'exists' => true
            ];
        }

        return new JsonResponse($results);
    }
    #[Route('/inventory/storagecard', name: 'inventory_storage')]
    public function createStoragecard(
        Request $request,
        FileUploader $fileUploader,
        Utility $utility,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        try {
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            $storagecard = new Storagecard();

            $user = $tokenStorage->getToken()->getUser();
            $form = $this->createForm(StoragecardRespType::class, $storagecard, [
                'method' => 'POST',
                'idSite' => $user->getIdSite()->getIdSite()
            ]);
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {

                $storagecard = $form->getData();

                // Définir la date de création
                $storagecard->setCreationDate(new \DateTime());

                // Récupérer l'état physique
                if ($form->has('stateType')) {
                    $stateType = $form->get('stateType')->getData();
                    $storagecard->setStateType($stateType);
                }

                $idShelvingunit = $form->get('idShelvingunit')->getData();
                $chimicalproduct = $form->get('idChimicalproduct')->getData();


                $utility->movedIsAuthorised($idShelvingunit, $chimicalproduct, $entityManager);

                $securityFile = $form->get('uploadedSecurityFile')->getData();
                $analysisFile = $form->get('uploadedAnalysisFile')->getData();


                //cette condition est necessaire car le champ "Fiche de prudence" n'est
                //pas obligatoire
                if ($securityFile != null) {
                    $newSecurityFileName =
                        $fileUploader->upload(
                            $securityFile,
                            $this->getParameter('idSecurityFile_directory'));

                    $securityfile = new Securityfile();
                    $securityfile->setNameSecurityfile($newSecurityFileName);
                    $entityManager->persist($securityfile);
                    $entityManager->flush();
                    $storagecard->setIdSecurityfile($securityfile);
                }


                //cette condition est necessaire car le champ "Certificat d'analyse" n'est
                //pas obligatoire
                if ($analysisFile != null) {
                    $newAnalysisFileName =
                        $fileUploader->upload(
                            $analysisFile,
                            $this->getParameter('idAnalysisFile_directory'));

                    $analysisFile = new Analysisfile();
                    $analysisFile->setNameAnalysisfile($newAnalysisFileName);
                    $entityManager->persist($analysisFile);
                    $entityManager->flush();
                    $storagecard->setIdAnalysisfile($analysisFile);
                }

                $entityManager->persist($storagecard);
                $entityManager->flush();

                //On initialise la position du produit
                $movedHistory = new Movedhistory();
                $movedHistory->setMovedate(new DateTime());
                $movedHistory->setIdShelvingunit($storagecard->getIdShelvingunit());
                $movedHistory->setIdStoragecard($storagecard);
                $movedHistory->setIdUser($tokenStorage->getToken()->getUser());

                //On passe l'historique à la base de donnée
                $entityManager->persist($movedHistory);
                $entityManager->flush();

                $this->addFlash('success',
                    'La fiche de stockage numéro ' . $storagecard->getIdStoragecard() . ' a été créée avec succès.');
                return $this->redirectToRoute('inventory_storage');
            }
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->render('inventory/storagecard.html.twig', [
                'form' => $form->createView(),
                'action' => 'create'
            ]);
        }
        catch (FileException $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue lors du déplacement du fichier.'
                .' Contactez votre administrateur.');
            //on redirige vers la page d'accueil
            return $this->redirectToRoute('home_page');
        }
        // S'il y a tout autre exception
//        catch (\Exception $e) {
//            $this->addFlash('error',
//                'Attention, une erreur est survenue.'
//                .' Contactez votre administrateur.');
//            return $this->redirectToRoute('home_page');
//        }
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/storagecard.html.twig', [
            'form' => $form->createView(),
            'action' => 'create'
        ]);
    }

    #[Route('/inventory/storagecard/{id}', name: 'inventory_storage_copy')]
    public function copyStoragecard(
        $id,
        Request $request,
        FileUploader $fileUploader,
        Utility $utility,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        try {
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            $storagecard = $repositoryStoragecard->find($id);
            $oldSecurityFile = $storagecard->getIdSecurityfile();
            $oldAnalysisFile = $storagecard->getIdAnalysisfile();

            $newStoragecard = new Storagecard();
            $newStoragecard->setStockquantity($storagecard->getStockquantity());
            $newStoragecard->setCapacity($storagecard->getCapacity());
            $newStoragecard->setPurity($storagecard->getPurity());
            $newStoragecard->setSerialnumber($storagecard->getSerialnumber());
            $newStoragecard->setTemperature($storagecard->getTemperature());
            $newStoragecard->setOpendate($storagecard->getOpendate());
            $newStoragecard->setExpirationdate($storagecard->getExpirationdate());
            $newStoragecard->setIsarchived($storagecard->getIsarchived());
            $newStoragecard->setIsrisked($storagecard->getIsrisked());
            $newStoragecard->setIspublished($storagecard->getIspublished());
            $newStoragecard->setIdChimicalproduct($storagecard->getIdChimicalproduct());
            $newStoragecard->setIdShelvingunit($storagecard->getIdShelvingunit());
            $newStoragecard->setIdProperty($storagecard->getIdProperty());
            $newStoragecard->setIdSupplier($storagecard->getIdSupplier());
            $newStoragecard->setReference($storagecard->getReference());
            // Copier l'état physique
            $newStoragecard->setStateType($storagecard->getStateType());

            $user = $tokenStorage->getToken()->getUser();
            $form = $this->createForm(StoragecardRespType::class, $newStoragecard, [
                'method' => 'POST',
                'idSite' => $user->getIdSite()->getIdSite(),
                'action' => 'copy'
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newStoragecard = $form->getData();

                // Définir la date de création
                $newStoragecard->setCreationDate(new \DateTime());

                // Récupérer l'état physique directement à partir des données soumises
                $formData = $request->request->get('storagecard_resp');
                if (isset($formData['stateType'])) {
                    $stateType = $formData['stateType'];
                    $newStoragecard->setStateType($stateType);
                }

                $idShelvingunit = $form->get('idShelvingunit')->getData();
                $chimicalproduct = $form->get('idChimicalproduct')->getData();

                $utility->movedIsAuthorised($idShelvingunit, $chimicalproduct, $entityManager);

                $securityFile = $form->get('uploadedSecurityFile')->getData();
                $analysisFile = $form->get('uploadedAnalysisFile')->getData();

                //cette condition est necessaire car le champ "Fiche de prudence" n'est
                //pas obligatoire
                if ($securityFile != null) {
                    $newSecurityFileName =
                        $fileUploader->upload(
                            $securityFile,
                            $this->getParameter('idSecurityFile_directory'));

                    $securityfile = new Securityfile();
                    $securityfile->setNameSecurityfile($newSecurityFileName);
                    $entityManager->persist($securityfile);
                    $entityManager->flush();
                    $newStoragecard->setIdSecurityfile($securityfile);
                }
                else {
                    $newStoragecard->setIdSecurityfile($oldSecurityFile);
                }

                //cette condition est necessaire car le champ "Certificat d'analyse" n'est
                //pas obligatoire
                if ($analysisFile != null) {
                    $newAnalysisFileName =
                        $fileUploader->upload(
                            $analysisFile,
                            $this->getParameter('idAnalysisFile_directory'));

                    $analysisFile = new Analysisfile();
                    $analysisFile->setNameAnalysisfile($newAnalysisFileName);
                    $entityManager->persist($analysisFile);
                    $entityManager->flush();
                    $newStoragecard->setIdAnalysisfile($analysisFile);
                }
                else{
                    $newStoragecard->setIdAnalysisfile($oldAnalysisFile);
                }
                $entityManager->persist($newStoragecard);
                $entityManager->flush();

                //On initialise la position du produit
                $movedHistory = new Movedhistory();
                $movedHistory->setMovedate(new DateTime());
                $movedHistory->setIdShelvingunit($newStoragecard->getIdShelvingunit());
                $movedHistory->setIdStoragecard($newStoragecard);
                $movedHistory->setIdUser($tokenStorage->getToken()->getUser());

                //On passe l'historique à la base de donnée
                $entityManager->persist($movedHistory);
                $entityManager->flush();

                $this->addFlash('success',
                    'La fiche de stockage numéro ' . $newStoragecard->getIdStoragecard() . ' a été créée avec succès.');
                return $this->redirectToRoute('inventory_storage');
            }
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->render('inventory/storagecard.html.twig', [
                'form' => $form->createView(),
                'action' => 'copy'
            ]);
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
            return $this->redirectToRoute('home_page');
        }
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/storagecard.html.twig', [
            'form' => $form->createView(),
            'action' => 'copy'
        ]);
    }

    #[Route('/inventory/archived/{id}', name: 'inventory_archived')]
    public function archivedStoragecard($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        $storagecard = $repositoryStoragecard->find($id);
        $storagecard->setIsarchived(true);
        $entityManager->persist($storagecard);
        $entityManager->flush();

        $this->addFlash('success',
            'La fiche de stockage a été archivée avec succès.');
        return $this->redirectToRoute('inventory');
    }

    #[Route('/inventory/modify/{id}', name: 'inventory_modify')]
    public function modifyStoragecard(
        $id,
        Request $request,
        FileUploader $fileUploader,
        Utility $utility,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response
    {
        try {
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            $storagecard = $repositoryStoragecard->find($id);
            $oldSecurityFile = $storagecard->getIdSecurityfile();
            $oldAnalysisFile = $storagecard->getIdAnalysisfile();
            $oldIdShelvingUnit = $storagecard->getIdShelvingunit()->getIdShelvingunit();

            $user = $tokenStorage->getToken()->getUser();
            $form = $this->createForm(StoragecardRespType::class, $storagecard, [
                'method' => 'POST',
                'idSite' => $user->getIdSite()->getIdSite()
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newStoragecard = $form->getData();

                // Récupérer l'état physique directement à partir des données soumises
                $formData = $request->request->get('storagecard_resp');
                if (isset($formData['stateType'])) {
                    $stateType = $formData['stateType'];
                    $newStoragecard->setStateType($stateType);
                }

                $chimicalproduct = $form->get('idChimicalproduct')->getData();

                $securityFile = $form->get('uploadedSecurityFile')->getData();
                $analysisFile = $form->get('uploadedAnalysisFile')->getData();

                // On vérifie si le produit a été déplacé
                if ($oldIdShelvingUnit != $newStoragecard->getIdShelvingunit()->getIdShelvingunit())
                {
                    $utility->movedIsAuthorised($newStoragecard->getIdShelvingunit(), $chimicalproduct, $entityManager);
                    //On enregistre le déplacement du produit
                    $movedHistory = new Movedhistory();
                    $movedHistory->setMovedate(new DateTime());
                    $movedHistory->setIdShelvingunit($newStoragecard->getIdShelvingunit());
                    $movedHistory->setIdStoragecard($newStoragecard);
                    $movedHistory->setIdUser($tokenStorage->getToken()->getUser());
                    //On passe l'historique à la base de donnée
                    $entityManager->persist($movedHistory);
                    $entityManager->flush();
                }
                //cette condition est necessaire car le champ "Fiche de prudence" n'est
                //pas obligatoire
                if ($securityFile != null) {
                    $newSecurityFileName =
                        $fileUploader->upload(
                            $securityFile,
                            $this->getParameter('idSecurityFile_directory'));

                    $securityfile = new Securityfile();
                    $securityfile->setNameSecurityfile($newSecurityFileName);
                    $entityManager->persist($securityfile);
                    $entityManager->flush();
                    $newStoragecard->setIdSecurityfile($securityfile);
                }
                else {
                    $newStoragecard->setIdSecurityfile($oldSecurityFile);
                }

                //cette condition est necessaire car le champ "Certificat d'analyse" n'est
                //pas obligatoire
                if ($analysisFile != null) {
                    $newAnalysisFileName =
                        $fileUploader->upload(
                            $analysisFile,
                            $this->getParameter('idAnalysisFile_directory'));

                    $analysisFile = new Analysisfile();
                    $analysisFile->setNameAnalysisfile($newAnalysisFileName);
                    $entityManager->persist($analysisFile);
                    $entityManager->flush();
                    $newStoragecard->setIdAnalysisfile($analysisFile);
                }
                else{
                    $newStoragecard->setIdAnalysisfile($oldAnalysisFile);
                }
                $entityManager->persist($newStoragecard);
                $entityManager->flush();

                $this->addFlash('success',
                    'La fiche de stockage a été modifiée avec succès.');
                return $this->redirectToRoute('inventory_make');
            }
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->render('inventory/storagecard.html.twig', [
                'form' => $form->createView(),
                'action' => 'modify'
            ]);
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
            return $this->redirectToRoute('home_page');
        }
        //Quoi qu'il arrive on rend la page initiale
        return $this->render('inventory/storagecard.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify'
        ]);
    }
}