<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'profil')]
    public function index(
        Request $request,
        UserInterface $user,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            $oldUserLogin = $user->getUserIdentifier();
            $oldUserPassword = $user->getPassword();
            $oldUserMail = $user->getMail();
            $oldUserStatus = $user->getIdStatus();
            $oldUserSite = $user->getIdSite();
            $oldUserEndrightdate = $user->getEndrightdate();

            $passwordIsRequired = false;
            $form = $this->createForm(UserType::class, $user, [
                'method' => 'POST',
                'requirePassword' => $passwordIsRequired]);

            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                //Si l'utilisateur est connecté avec le CAS
                //Il ne peut modifier que son nom et prénom
                if ($oldUserPassword == null){
                    $user = $form->getData();

                    $user->setPassword($oldUserPassword);
                    $user->setUsername($oldUserLogin);
                    $user->setMail($oldUserMail);
                }
                // Sinon
                else{
                    $user = $form->getData();
                    //On récupère la valeur du mot de passe dans le formulaire
                    $plainPassword = $form->get('plainPassword')->getData();
                    //S'il est null ou vide,
                    //on récupère ce qui était dans la base de donnée
                    if ($plainPassword == null || empty($plainPassword)){
                        $user->setPassword($oldUserPassword);
                    }
                    //Sinon, on lui attribut ce qui a été saisi
                    else {
                        $user->setPassword(
                            $passwordHasher->hashPassword(
                                $user,
                                $plainPassword
                            )
                        );
                    }
                }
                //Quoi qu'il arrive, aucun utilisateur ne peut modifier
                //son statut et sa date de fin de droits
                $user->setIdStatus($oldUserStatus);
                $user->setIdSite($oldUserSite);
                $user->setEndrightdate($oldUserEndrightdate);

                $errors = $validator->validate($user);
                if (count($errors) > 0) {
                    return $this->redirectToRoute('profil', [
                        'errors' => $errors
                    ]);
                }

                //On donne toutes les info au gestionnaire de la base
                $entityManager->persist($user);
                $entityManager->flush();
                //On enregistre un message de réussite
                $this->addFlash('success', 'Votre profil a été modifié avec succès.');
                return $this->redirectToRoute('profil');
            }
        }
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                "Un utilisateur possède déjà ce login ou mail. "
                ."Le profil n'a pas pu être modifié.");
        }
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue. Vous n\'avez pas pu '
                .'modifier votre profil. Contactez votre administrateur.');

            return $this->redirectToRoute('home_page');
        }
        return $this->render('profil/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}