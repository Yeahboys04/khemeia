<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\PropertyType;
use App\Entity\Property;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Controlle la page de gestion des propriétaires
 */
class AdminPropertyController extends AbstractController
{
    /**
     * Créer un propriétaire via un formulaire
     */
    #[Route('/admin/property', name: 'admin_property')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            //Initialise le repository pour la base de données
            $repository = $entityManager->getRepository(Property::class);

            //Cherche tous les utilisateurs
            $properties = $repository->findAll();

            //Initialise le formulaire
            $property = new Property();
            $form = $this->createForm(PropertyType::class, $property, [
                'method' => 'POST'
            ]);

            $form->handleRequest($request);
            //Si le formulaire est soumis est valide
            if ($form->isSubmitted() && $form->isValid()) {
                //on récupère les données
                $property = $form->getData();
                //on crypte le mot de passe

                //On passe le propriétaire à la base de données grâce à doctrine
                $entityManager->persist($property);
                $entityManager->flush();
                //On enregistre un message flash
                $this->addFlash('success',
                    'Le propriétaire a été créé avec succès.');

                //On renvoi la page initiale
                return $this->redirectToRoute('admin_property');
            }
        }
            //S'il y a une exception de clé dupliquée
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                'Attention, un propriétaire possède déjà ce nom'
                .'. Le propriétaire n\'a pas pu etre créé.');
            return $this->redirectToRoute('admin_property');
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
        return $this->render('admin/property.html.twig', [
            'form' => $form->createView(),
            'action' => 'create',
            'properties' => $properties,
        ]);
    }

    /**
     * Modifier un propriétaire via un formulaire
     */
    #[Route('/admin/property/modify/{id}', name: 'admin_property_modify')]
    public function modify(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        try {
            $repository = $entityManager->getRepository(Property::class);

            $properties = $repository->findAll();

            $previousProperty = $repository->find($id);

            if ($previousProperty != null || !empty($previousProperty) ){

                $form = $this->createForm(PropertyType::class, $previousProperty, [
                    'method' => 'POST',
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $property = $form->getData();

                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le propriétaire a été modifié avec succès.');

                    return $this->redirectToRoute('admin_property');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le propriétaire '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_property');
            }
        }
        catch (UniqueConstraintViolationException $ucve){
            $this->addFlash('error',
                "Un propriétaire possède déjà ce nom. "
                ."Le propriétaire n'a pas pu être modifié.");
            return $this->redirectToRoute('admin_property_modify');
        }
        catch (\Exception $e) {
            $this->addFlash('error',
                'Attention, une erreur est survenue. Le propriétaire '
                .'n\'a pas pu être modifié. Contactez votre administrateur.');

            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/property.html.twig', [
            'form' => $form->createView(),
            'action' => 'modify',
            'properties' => $properties,
            'id' => $id
        ]);
    }

    /**
     * Supprimer un propriétaire
     */
    #[Route('/admin/property/remove/{id}', name: 'admin_property_remove')]
    public function remove(
        Request $request,
        EntityManagerInterface $entityManager,
        $id
    ): Response {
        try {
            $repository = $entityManager->getRepository(Property::class);

            $properties = $repository->findAll();

            $property = $repository->find($id);

            if ($property != null || !empty($property) ){

                $form = $this->createForm(PropertyType::class, $property, [
                    'method' => 'POST',
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $entityManager->remove($property);
                    $entityManager->flush();
                    $this->addFlash('success',
                        'Le propriétaire a été supprimé avec succès.');

                    return $this->redirectToRoute('admin_property');
                }
            }
            else {
                $this->addFlash('error',
                    'Attention, une erreur est survenue. Le propriétaire '
                    .' N° \' ' .$id. ' \' n\'existe pas.');

                return $this->redirectToRoute('admin_property');
            }
        }

        catch (\Exception $e) {
            $this->addFlash('error', 'Attention, une erreur est '
                .'survenue. Le propriétaire n\'a pas pu être supprimé.'
                .' Contactez votre administrateur.');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('admin/property.html.twig', [
            'form' => $form->createView(),
            'action' => 'remove',
            'properties' => $properties,
            'id' => $id
        ]);
    }
}