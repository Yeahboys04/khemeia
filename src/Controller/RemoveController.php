<?php

namespace App\Controller;

use App\Entity\Movedhistory;
use App\Entity\Storagecard;
use App\Entity\Tracability;
use App\Entity\User;
use App\Form\QuantityType;
use App\Form\SearchType;
use App\Service\Utility;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RemoveController extends AbstractController
{
    #[Route('/remove', name: 'remove')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Initialise le repository pour la base de données
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        // Initialise le formulaire
        $storagecard = new Storagecard();

        $form = $this->createForm(SearchType::class, $storagecard, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le type de recherche (produit ou CAS)
            $searchType = $form->get('searchType')->getData();

            // Récupère l'option de recherche sur tous les sites
            $searchAll = $form->get('searchAll')->getData();

            // Récupère le site sélectionné seulement si on ne cherche pas sur tous les sites
            $site = null;
            if ($searchAll === 'non') {
                $site = $form->get('idSite')->getData();
            }


            // Récupère le produit chimique selon le type de recherche
            if ($searchType === 'cas') {
                // Recherche par numéro CAS
                $chimicalProduct = $form->get('casSearch')->getData();


            } else {
                // Recherche par nom de produit (par défaut)
                $chimicalProduct = $form->get('idChimicalproduct')->getData();
            }



            // Si aucun produit n'est sélectionné, affiche un message d'erreur
            if (!$chimicalProduct) {
                $this->addFlash('error', 'Veuillez sélectionner un produit.');
                return $this->render('remove/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Recherche les produits correspondants (selon option tous sites ou site spécifique)
            if ($searchAll === 'oui') {
                // Fonction à créer dans le repository ou utilisation de findBy
                $storagecards = $repositoryStoragecard->loadStorageCardsByCAS($chimicalProduct->getCasnumber());
            } else {
                $storagecards = $repositoryStoragecard->loadStorageCardBySite($site, $chimicalProduct);
            }

            // Rend la page avec les résultats
            return $this->render('remove/index.html.twig', [
                'form' => $form->createView(),
                'storagecards' => $storagecards,
                'site' => $site
            ]);
        }

        // Rend la page initiale
        return $this->render('remove/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Les autres méthodes restent inchangées
    #[Route('/remove/{id}', name: 'remove_quantity')]
    public function removeQuantity(
        $id,
        Request $request,
        Utility $utility,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        try {
            //Initialise le repository pour la base de données
            $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

            //Cherche la fiche produit
            $storagecard = $repositoryStoragecard->find($id);
            $stockQuantity = $storagecard->getStockquantity();
            $openDate = $storagecard->getOpendate();
            $expirationDate = $storagecard->getExpirationdate();

            //Initialise le formulaire
            $form = $this->createForm(QuantityType::class, $storagecard, [
                'method' => 'POST',
                'idSite' => $storagecard->getIdShelvingunit()
                    ->getIdCupboard()
                    ->getIdStock()
                    ->getIdSite()
                    ->getIdSite(),
                'stockquantity' => $stockQuantity
            ]);

            $form->handleRequest($request);
            //Si le formulaire est soumis et valide
            if ($form->isSubmitted()) {
                //on récupère les données
                $chimicalproduct = $storagecard->getIdChimicalproduct();

                $retiredQuantity = $form->get('retiredquantity')->getData();
                $isMoved = $form->get('ismoved')->getData();
                $isOpened = $form->get('isopened')->getData();
                $idShelvingunit = $form->get('idShelvingunit')->getData();
                $user = $tokenStorage->getToken()->getUser();

                //Si le produit a été déplacé
                if($isMoved){
                    $utility->movedIsAuthorised($idShelvingunit, $chimicalproduct, $entityManager);
                    //On se souvient du déplacement
                    $movedHistory = new Movedhistory();
                    $movedHistory->setMovedate(new DateTime());
                    $movedHistory->setIdShelvingunit($idShelvingunit);
                    $movedHistory->setIdStoragecard($storagecard);
                    $movedHistory->setIdUser($user);

                    //On actualise la fiche de stockage
                    $storagecard->setIdShelvingunit($idShelvingunit);

                    //On passe l'historique à la base de donnée
                    $entityManager->persist($movedHistory);
                    $entityManager->flush();
                }

                if(!$isOpened){
                    $storagecard->setOpendate($openDate);
                    $storagecard->setExpirationdate($expirationDate);
                }
                //On se souvient que l'utilisateur a été en contact avec ce produit
                $tracability = new Tracability();
                $tracability->setRetiredate(new DateTime());
                $tracability->setRetirequantity($retiredQuantity);
                $tracability->setIdStoragecard($storagecard);
                $tracability->setIdUser($user);
                $entityManager->persist($tracability);

                //On actualise la fiche de stockage
                //Si aucune quantité n'a été retirée et que la quantité initiale est nulle,
                //On passe null à la base de données
                if ($stockQuantity == null){
                    $storagecard->setStockquantity(null);
                }
                else{
                    $storagecard->setStockquantity($stockQuantity-$retiredQuantity);
                }
                $entityManager->persist($storagecard);
                $entityManager->flush();
                $this->addFlash('success',
                    'La quantité de produit a bien été retirée.');
                return $this->redirectToRoute('remove_quantity', [
                    'id' => $id,
                ]);
            }

            return $this->render('remove/remove.html.twig', [
                'form' => $form->createView(),
                'id' => $id,
                'storagecard' => $storagecard
            ]);
        }
        catch (LogicException $le) {
            $this->addFlash('error', $le->getMessage());
            return $this->render('remove/remove.html.twig', [
                'form' => $form->createView(),
                'id' => $id,
                'storagecard' => $storagecard
            ]);
        }

        return $this->render('remove/remove.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'storagecard' => $storagecard
        ]);
    }

    #[Route('/remove/ask/{id}', name: 'remove_ask')]
    public function askForRemove(
        $id,
        Request $request,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        //Initialise le repository pour la base de données
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);

        //Cherche la fiche produit
        $storagecard = $repositoryStoragecard->find($id);
        $stockQuantity = $storagecard->getStockquantity();

        $site = $storagecard->getIdShelvingunit()
            ->getIdCupboard()
            ->getIdStock()
            ->getIdSite()
            ->getIdSite();
        //Initialise le formulaire
        $form = $this->createForm(QuantityType::class, $storagecard, [
            'method' => 'POST',
            'idSite' => $site,
            'stockquantity' => $stockQuantity
        ]);

        $repositoryUser = $entityManager->getRepository(User::class);
        $supervisors = $repositoryUser->loadSupervisors($site);
        $mails = [];
        foreach($supervisors as $supervisor){
            $mails[] = $supervisor->getMail();
        }
        $user = $tokenStorage->getToken()->getUser();

        $email = (new Email())
            ->from('contact@khemeia.fr')
            ->to(...$mails)
            ->subject('Khemeia - Demande de retrait de produit')
            ->html(
                $this->renderView(
                // templates/emails/askremoveproduct.html.twig
                    'emails/askremoveproduct.html.twig',
                    [
                        'user' => $user,
                        'date' => new DateTime(),
                        'storagecard' => $storagecard
                    ]
                )
            );

        $mailer->send($email);
        $this->addFlash('success',
            'Votre demande a été transmise au responsable.');
        return $this->redirectToRoute('remove_quantity', [
            'id' => $id,
        ]);
    }
}