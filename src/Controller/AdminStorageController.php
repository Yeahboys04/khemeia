<?php

namespace App\Controller;

use App\Entity\Analysisfile;
use App\Entity\Movedhistory;
use App\Entity\Securityfile;
use App\Entity\Storagecard;
use App\Form\StoragecardRespType;
use App\Form\StoragecardType;
use App\Service\FileUploader;
use App\Service\Utility;
use DateTime;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminStorageController extends AbstractController
{
    #[Route('/admin/storage', name: 'admin_storage')]
    public function index(
        Request $request,
        FileUploader $fileUploader,
        Utility $utility,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        try {
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            $storagecards = $repositoryStoragecard->findAll();
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
                return $this->redirectToRoute('admin_storage');
            }
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->render('admin/storage.html.twig', [
                'form' => $form->createView(),
                'action' => 'create',
                'storagecards' => $storagecards,
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
        // catch (\Exception $e) {
        //     $this->addFlash('error',
        //         'Attention, une erreur est survenue.'
        //         .' Contactez votre administrateur.');
        //     return $this->redirectToRoute('home_page');
        // }

        return $this->render('admin/storage.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'storagecards' => $storagecards,
        ]);
    }

    /**
     * Modifier une fiche de stockage via un formulaire
     */
    #[Route('/admin/storage/modify/{id}', name: 'admin_storage_modify')]
    public function modify(
        Request $request,
        FileUploader $fileUploader,
        Utility $utility,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        $id
    ): Response {
        try {
            //Initialise le repositoryStoragecard pour la base de données
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            //Cherche tous les storagecards
            $storagecards = $repositoryStoragecard->findAll();

            $previousStoragecard = $repositoryStoragecard->find($id);
            $previousLocalisation = $previousStoragecard->getIdShelvingunit();
            $user = $tokenStorage->getToken()->getUser();

            if ($previousStoragecard != null || !empty($previousStoragecard)) {
                $form = $this->createForm(StoragecardRespType::class, $previousStoragecard, [
                    'method' => 'POST',
                    'idSite' => $user->getIdSite()->getIdSite()
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $storagecard = $form->getData();

                    // Récupérer l'état physique directement à partir des données soumises
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

                    //On vérifie si le produit a été déplacé:
                    $localisation = $form->get('idShelvingunit')->getData();
                    //Si le produit a été déplacé
                    if ($localisation !== $previousLocalisation){
                        //On se souvient du déplacement du produit
                        $movedHistory = new Movedhistory();
                        $movedHistory->setMovedate(new DateTime());
                        $movedHistory->setIdShelvingunit($localisation);
                        $movedHistory->setIdStoragecard($storagecard);
                        $movedHistory->setIdUser($tokenStorage->getToken()->getUser());

                        //On passe l'historique à la base de donnée
                        $entityManager->persist($movedHistory);
                        $entityManager->flush();
                    }

                    $entityManager->flush();
                    $this->addFlash('success',
                        'La fiche de stockage a été modifiée avec succès.');

                    return $this->redirectToRoute('admin_storage');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. La fiche de stockage '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_storage');
            }
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->render('admin/storage.html.twig', [
                'form' => $form->createView(),
                'action' => 'modify',
                'storagecards' => $storagecards,
                'id' => $id
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
            throw $e;
        }

        return $this->render('admin/storage.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'storagecards' => $storagecards,
            'id' => $id
        ]);
    }

    /**
     * Dupliquer une fiche de stockage via un formulaire
     */
    #[Route('/admin/storage/duplicate/{id}', name: 'admin_storage_duplicate')]
    public function duplicate(
        Request $request,
        FileUploader $fileUploader,
        Utility $utility,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        $id
    ): Response {
        try {
            // Initialise le repository pour la base de données
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            // Cherche tous les storagecards pour l'affichage de la liste
            $storagecards = $repositoryStoragecard->findAll();

            // Récupère la fiche originale à dupliquer
            $originalStoragecard = $repositoryStoragecard->find($id);
            $user = $tokenStorage->getToken()->getUser();

            if ($originalStoragecard === null || empty($originalStoragecard)) {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. La fiche de stockage '
                    .' N° \' ' .$id. ' \' n\'existe pas.');
                return $this->redirectToRoute('admin_storage');
            }

            // Créer une nouvelle fiche basée sur l'originale
            $newStoragecard = new Storagecard();
            // Copier les propriétés de l'original
            $newStoragecard->setIdChimicalproduct($originalStoragecard->getIdChimicalproduct());
            $newStoragecard->setIdShelvingunit($originalStoragecard->getIdShelvingunit());
            $newStoragecard->setCapacity($originalStoragecard->getCapacity());
            $newStoragecard->setStockquantity($originalStoragecard->getStockquantity());
            $newStoragecard->setPurity($originalStoragecard->getPurity());
            $newStoragecard->setTemperature($originalStoragecard->getTemperature());
            $newStoragecard->setSerialnumber($originalStoragecard->getSerialnumber());
            $newStoragecard->setOpendate(new \DateTime());  // Date d'ouverture actuelle
            $newStoragecard->setExpirationdate($originalStoragecard->getExpirationdate());
            $newStoragecard->setIdProperty($originalStoragecard->getIdProperty());
            $newStoragecard->setIdSupplier($originalStoragecard->getIdSupplier());
            $newStoragecard->setReference($originalStoragecard->getReference());
            $newStoragecard->setIsarchived(false); // Par défaut non archivé
            $newStoragecard->setIsrisked($originalStoragecard->getIsrisked());
            $newStoragecard->setIspublished($originalStoragecard->getIspublished());
            $newStoragecard->setCommentary($originalStoragecard->getCommentary() . ' (Copie)');
            $newStoragecard->setStateType($originalStoragecard->getStateType()); // Copier l'état physique

            // Créer le formulaire avec la nouvelle fiche pré-remplie
            $form = $this->createForm(StoragecardRespType::class, $newStoragecard, [
                'method' => 'POST',
                'idSite' => $user->getIdSite()->getIdSite(),
                'action' => 'copy'
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupérer les données du formulaire
                $newStoragecard = $form->getData();
                $newStoragecard->setCreationDate(new \DateTime()); // Définir la date de création

                // Récupérer l'état physique directement à partir des données soumises
                if ($form->has('stateType')) {
                    $stateType = $form->get('stateType')->getData();
                    $newStoragecard->setStateType($stateType);
                }

                $idShelvingunit = $form->get('idShelvingunit')->getData();
                $chimicalproduct = $form->get('idChimicalproduct')->getData();

                $utility->movedIsAuthorised($idShelvingunit, $chimicalproduct, $entityManager);

                // Traitement des fichiers comme dans la méthode index (création)
                $securityFile = $form->get('uploadedSecurityFile')->getData();
                $analysisFile = $form->get('uploadedAnalysisFile')->getData();

                if ($securityFile != null) {
                    $newSecurityFileName = $fileUploader->upload(
                        $securityFile,
                        $this->getParameter('idSecurityFile_directory'));

                    $securityfile = new Securityfile();
                    $securityfile->setNameSecurityfile($newSecurityFileName);
                    $entityManager->persist($securityfile);
                    $entityManager->flush();
                    $newStoragecard->setIdSecurityfile($securityfile);
                } else {
                    // Utiliser les fichiers de l'original si aucun nouveau n'est téléchargé
                    $newStoragecard->setIdSecurityfile($originalStoragecard->getIdSecurityfile());
                }

                if ($analysisFile != null) {
                    $newAnalysisFileName = $fileUploader->upload(
                        $analysisFile,
                        $this->getParameter('idAnalysisFile_directory'));

                    $analysisFile = new Analysisfile();
                    $analysisFile->setNameAnalysisfile($newAnalysisFileName);
                    $entityManager->persist($analysisFile);
                    $entityManager->flush();
                    $newStoragecard->setIdAnalysisfile($analysisFile);
                } else {
                    // Utiliser les fichiers de l'original si aucun nouveau n'est téléchargé
                    $newStoragecard->setIdAnalysisfile($originalStoragecard->getIdAnalysisfile());
                }

                // Persister la nouvelle fiche
                $entityManager->persist($newStoragecard);
                $entityManager->flush();

                // Créer l'historique de mouvement
                $movedHistory = new Movedhistory();
                $movedHistory->setMovedate(new DateTime());
                $movedHistory->setIdShelvingunit($newStoragecard->getIdShelvingunit());
                $movedHistory->setIdStoragecard($newStoragecard);
                $movedHistory->setIdUser($tokenStorage->getToken()->getUser());

                $entityManager->persist($movedHistory);
                $entityManager->flush();

                $this->addFlash('success', 'La fiche de stockage a été dupliquée avec succès.');
                return $this->redirectToRoute('admin_storage');
            }
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->render('admin/storage.html.twig', [
                'form' => $form->createView(),
                'action' => 'duplicate',
                'storagecards' => $storagecards,
                'id' => $id
            ]);
        }
        catch (FileException $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue lors du déplacement du fichier.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        // Afficher le formulaire de duplication
        return $this->render('admin/storage.html.twig', [
            'form' => $form->createView(),
            'action' => 'duplicate',
            'storagecards' => $storagecards,
            'id' => $id,
            'originalStoragecard' => $originalStoragecard // Pour référence si nécessaire
        ]);
    }

    /**
     * Supprimer une fiche de stockage
     */
    #[Route('/admin/storage/remove/{id}', name: 'admin_storage_remove')]
    public function remove(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            $storagecards = $repositoryStoragecard->findAll();
            $storagecard = $repositoryStoragecard->find($id);

            if ($storagecard != null || !empty($storagecard)) {
                $form = $this->createForm(StoragecardRespType::class, $storagecard, [
                    'method' => 'POST',
                ]);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager->remove($storagecard);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'La fiche de stockage a été supprimée avec succès.');
                    return $this->redirectToRoute('admin_storage');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. La fiche de stockage '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_storage');
            }
        }
        catch(ForeignKeyConstraintViolationException $fkcve) {
            $this->addFlash('error',
                'Impossible de supprimer cette fiche. Il existe un historique d\'utilisation de ce produit pour un utilisateur.'
                .' Archivez cette fiche ou contactez votre administrateur.');
            return $this->redirectToRoute('admin_storage');
        }
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/storage.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'storagecards' => $storagecards,
            'id' => $id
        ]);
    }
}