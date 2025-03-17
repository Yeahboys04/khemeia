<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\UserType;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controlle la page de gestion des utilisateurs
 */
class AdminUserController extends AbstractController
{
    /**
     * Créer un utilisateur via un formulaire
     */
    #[Route('/admin/users', name: 'admin_user')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            //Initialise le repository pour la base de données
            $repository = $entityManager->getRepository(User::class);

            //Cherche tous les utilisateurs actifs
            $users = $repository->findAllActive();

            //Initialise le formulaire
            $user = new User();
            $passwordIsRequired = true;
            $form = $this->createForm(UserType::class, $user, [
                'method' => 'POST',
                'requirePassword' => $passwordIsRequired,
            ]);

            $form->handleRequest($request);
            //Si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                //on récupère les données
                $user = $form->getData();

                // Vérifier si un utilisateur archivé existe avec le même login ou email
                $archivedUser = $repository->findArchivedUser($user->getUsername(), $user->getMail());
                if ($archivedUser) {
                    $this->addFlash('warning',
                        'Un compte utilisateur archivé existe déjà avec ce login ou cet email. ' .
                        'Veuillez désarchiver l\'utilisateur existant au lieu d\'en créer un nouveau.');

                    return $this->redirectToRoute('admin_user_archived');
                }

                //on crypte le mot de passe
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $errors = $validator->validate($user);
                if (count($errors) > 0) {
                    return $this->redirectToRoute('admin_user', [
                        'errors' => $errors
                    ]);
                }
                //On passe l'utilisateur à la base de données grâce à doctrine
                $entityManager->persist($user);
                $entityManager->flush();
                //On enregistre un message flash
                $this->addFlash('success',
                    'L\'utilisateur a été créé avec succès.');

                //On renvoi la page initiale
                return $this->redirectToRoute('admin_user');
            }
        }
            //S'il y a une exception de clé dupliquée
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                'Attention, un utilisateur possède déjà ce login ou'
                .' mail. L\'utilisateur n\'a pas pu etre créé.');
            return $this->redirectToRoute('admin_user');
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
        return $this->render('admin/user.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'users' => $users,
        ]);
    }

    /**
     * Modifier un utilisateur via un formulaire
     */
    #[Route('/admin/users/modify/{id}', name: 'admin_user_modify')]
    public function modify(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        $id
    ): Response {
        try {
            $repository = $entityManager->getRepository(User::class);

            $users = $repository->findAllActive();

            $previousUser = $repository->find($id);

            if ($previousUser != null || !empty($previousUser) ){
                $passwordIsRequired = false;
                $previousPassword = $previousUser->getPassword();
                //Si le mot de passe est null, on envoi un champs vide
                if ($previousPassword == null){
                    $form = $this->createForm(UserType::class, $previousUser, [
                        'method' => 'POST',
                        'requirePassword' => $passwordIsRequired,
                        'emptyPassword' => '',
                    ]);
                }
                //Sinon on envoi le mot de passe précédent
                else{
                    $form = $this->createForm(UserType::class, $previousUser, [
                        'method' => 'POST',
                        'requirePassword' => $passwordIsRequired,
                        'emptyPassword' => $previousPassword,
                    ]);
                }

                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    $user = $form->getData();
                    //On récupère la valeur du mot de passe dans le formulaire
                    $formPassword = $form->get('password')->getData();
                    //S'il est null ou vide,
                    //on récupère ce qui était dans la base de donnée
                    if ($formPassword == null || empty($formPassword)){
                        $user->setPassword($previousPassword);
                    }
                    //Sinon, on lui attribut ce qui a été saisi
                    else {
                        $user->setPassword(
                            $passwordHasher->hashPassword(
                                $user,
                                $formPassword
                            )
                        );
                    }
                    $errors = $validator->validate($user);
                    if (count($errors) > 0) {
                        return $this->redirectToRoute('admin_user', [
                            'errors' => $errors
                        ]);
                    }

                    $entityManager->flush();
                    $this->addFlash('success',
                        'L\'utilisateur a été modifié avec succès.');

                    return $this->redirectToRoute('admin_user');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. L\'utilisateur '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_user');
            }
        }
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                "Un utilisateur possède déjà ce login ou mail. "
                ."L'utilisateur n'a pas pu être modifié.");
            return $this->redirectToRoute('admin_user_modify');
        }
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue. L\'utilisateur '
                .'n\'a pas pu être modifié. Contactez votre administrateur.');

            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/user.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'users' => $users,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un utilisateur
     */
    #[Route('/admin/users/remove/{id}', name: 'admin_user_remove')]
    public function remove(
        Request $request,
        EntityManagerInterface $entityManager,
        $id
    ): Response {
        try {
            $repository = $entityManager->getRepository(User::class);

            $users = $repository->findAllActive();

            $user = $repository->find($id);

            if ($user != null || !empty($user) ){

                $passwordIsRequired = false;
                $form = $this->createForm(UserType::class, $user, [
                    'method' => 'POST',
                    'requirePassword' => $passwordIsRequired,
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {

                    $entityManager->remove($user);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'L\'utilisateur a été supprimé avec succès.');

                    return $this->redirectToRoute('admin_user');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. L\'utilisateur '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_user');
            }
        }
        catch (ForeignKeyConstraintViolationException $fkcve){
            $this->addFlash('error', 'Attention, l\'utilisateur '
                .'n\'a pas pu être supprimé car il est relié à un historique d\'utilisation'
                .' ou de retrait.');
            return $this->redirectToRoute('home_page');
        }

        catch (\Exception $e) {
            $this->addFlash('error', 'Attention, une erreur est '
                .'survenue. L\'utilisateur n\'a pas pu être supprimé.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/user.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'users' => $users,
            'id' => $id
        ]);
    }

    /**
     * Archiver un utilisateur
     */
    #[Route('/admin/users/archive/{id}', name: 'admin_user_archive')]
    public function archive(
        EntityManagerInterface $entityManager,
                               $id
    ): Response {
        try {
            $repository = $entityManager->getRepository(User::class);
            $user = $repository->find($id);

            if ($user !== null) {
                $user->archive();
                $entityManager->flush();
                $this->addFlash('success', 'L\'utilisateur a été archivé avec succès.');
            } else {
                $this->addFlash('error', 'Utilisateur introuvable.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'archivage de l\'utilisateur.');
        }

        return $this->redirectToRoute('admin_user');
    }

    /**
     * Afficher les utilisateurs archivés
     */
    #[Route('/admin/users/archived', name: 'admin_user_archived')]
    public function showArchived(
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $repository = $entityManager->getRepository(User::class);
            $archivedUsers = $repository->findAllArchived();

            return $this->render('admin/user_archived.html.twig', [
                'archivedUsers' => $archivedUsers,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération des utilisateurs archivés.');
            return $this->redirectToRoute('admin_user');
        }
    }

    /**
     * Désarchiver un utilisateur
     */
    #[Route('/admin/users/unarchive/{id}', name: 'admin_user_unarchive')]
    public function unarchive(
        EntityManagerInterface $entityManager,
                               $id
    ): Response {
        try {
            $repository = $entityManager->getRepository(User::class);
            $user = $repository->find($id);

            if ($user !== null) {
                $user->unarchive();
                $entityManager->flush();
                $this->addFlash('success', 'L\'utilisateur a été désarchivé avec succès.');
            } else {
                $this->addFlash('error', 'Utilisateur introuvable.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la désarchivation de l\'utilisateur.');
        }

        return $this->redirectToRoute('admin_user_archived');
    }
}