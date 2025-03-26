<?php

namespace App\Service;

use App\Entity\Movedhistory;
use App\Entity\Storagecard;
use App\Entity\Tracability;
use App\Form\QuantityType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Service pour gérer les retraits de produits
 */
class RemoveService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Utility $utility
    ) {
    }

    /**
     * Gère le formulaire de retrait de produit
     *
     * @return array Résultats du traitement du formulaire
     */
    public function handleRemoveForm(Request $request, Storagecard $storagecard): array
    {
        $stockQuantity = $storagecard->getStockquantity();
        $openDate = $storagecard->getOpendate();
        $expirationDate = $storagecard->getExpirationdate();

        $form = $this->formFactory->create(QuantityType::class, $storagecard, [
            'method' => 'POST',
            'idSite' => $storagecard->getIdShelvingunit()
                ->getIdCupboard()
                ->getIdStock()
                ->getIdSite()
                ->getIdSite(),
            'stockquantity' => $stockQuantity
        ]);

        $form->handleRequest($request);

        $result = [
            'form' => $form,
            'processed' => false,
            'success' => false,
        ];

        if (!$form->isSubmitted()) {
            return $result;
        }

        $result['processed'] = true;

        $chimicalproduct = $storagecard->getIdChimicalproduct();
        $retiredQuantity = $form->get('retiredquantity')->getData();
        $isMoved = $form->get('ismoved')->getData();
        $isOpened = $form->get('isopened')->getData();
        $idShelvingunit = $form->get('idShelvingunit')->getData();
        $user = $this->tokenStorage->getToken()->getUser();

        // Si le produit a été déplacé
        if ($isMoved) {
            $this->utility->movedIsAuthorised($idShelvingunit, $chimicalproduct, $this->entityManager);

            // Créer l'historique de déplacement
            $movedHistory = new Movedhistory();
            $movedHistory->setMovedate(new DateTimeImmutable());
            $movedHistory->setIdShelvingunit($idShelvingunit);
            $movedHistory->setIdStoragecard($storagecard);
            $movedHistory->setIdUser($user);

            // Mettre à jour l'emplacement dans la fiche de stockage
            $storagecard->setIdShelvingunit($idShelvingunit);

            $this->entityManager->persist($movedHistory);
            $this->entityManager->flush();
        }

        // Gérer les dates d'ouverture et d'expiration
        if (!$isOpened) {
            $storagecard->setOpendate($openDate);
            $storagecard->setExpirationdate($expirationDate);
        }

        // Créer l'enregistrement de traçabilité
        $tracability = new Tracability();
        $tracability->setRetiredate(new DateTimeImmutable());
        $tracability->setRetirequantity($retiredQuantity);
        $tracability->setIdStoragecard($storagecard);
        $tracability->setIdUser($user);
        $this->entityManager->persist($tracability);

        // Mettre à jour la quantité en stock
        if ($stockQuantity === null) {
            $storagecard->setStockquantity(null);
        } else {
            $storagecard->setStockquantity($stockQuantity - $retiredQuantity);
        }

        $this->entityManager->persist($storagecard);
        $this->entityManager->flush();

        $result['success'] = true;
        return $result;
    }
}