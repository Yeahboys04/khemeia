<?php

namespace App\Service;

use App\Entity\Chimicalproduct;
use App\Entity\Controlbytype;
use App\Entity\Shelvingunit;
use App\Entity\Storagecard;
use App\Repository\ControlbytypeRepository;
use App\Repository\StoragecardRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class Utility
{
    /**
     * Vérifie si le placement d'un produit chimique dans une unité de rangement est autorisé
     * basé sur les règles de compatibilité entre les types de produits
     *
     * @param Shelvingunit $shelvingunit L'unité de rangement où placer le produit
     * @param Chimicalproduct $chimicalProduct Le produit chimique à placer
     * @param EntityManagerInterface $entityManager L'entity manager pour accéder aux repositories
     * @throws LogicException Si le placement n'est pas autorisé
     * @return bool True si le placement est autorisé
     */
    public function movedIsAuthorised(
        Shelvingunit $shelvingunit,
        Chimicalproduct $chimicalProduct,
        EntityManagerInterface $entityManager
    ): bool {
        // Récupérer les repositories nécessaires
        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $repositoryControlByType = $entityManager->getRepository(Controlbytype::class);

        // Obtenir tous les produits déjà présents dans cette unité de rangement
        $existingStoragecards = $repositoryStoragecard->loadStorageCardByShelvingunit($shelvingunit->getIdShelvingunit());

        // Si aucun produit n'est présent, le placement est autorisé
        if (empty($existingStoragecards)) {
            return true;
        }

        // Récupérer les types du nouveau produit
        $types1 = $chimicalProduct->getIdType();

        // Si le nouveau produit n'a pas de types, le placement est autorisé
        if ($types1->isEmpty()) {
            return true;
        }

        // Vérifier la compatibilité avec chaque produit existant
        foreach ($existingStoragecards as $existingCard) {
            $existingChimicalProduct = $existingCard->getIdChimicalproduct();

            // Si le produit existant n'a pas de types, continuer vers le suivant
            $types2 = $existingChimicalProduct->getIdType();
            if ($types2->isEmpty()) {
                continue;
            }

            // Vérifier les combinaisons de types pour incompatibilité
            foreach ($types1 as $type1) {
                foreach ($types2 as $type2) {
                    // Utiliser la méthode personnalisée du repository
                    /** @var ControlbytypeRepository $controlRepo */
                    $controlRepo = $repositoryControlByType;
                    $isCompatible = $controlRepo->areTypesCompatible(
                        $type1->getIdType(),
                        $type2->getIdType()
                    );

                    // Si une incompatibilité est explicitement définie (isCompatible = false)
                    if ($isCompatible === false) {
                        throw new LogicException(
                            "Le produit " . $chimicalProduct->getNameChimicalproduct() .
                            " de type " . $type1->getNameType() .
                            " ne peut pas être placé avec " . $existingChimicalProduct->getNameChimicalproduct() .
                            " de type " . $type2->getNameType() .
                            " car ils sont incompatibles."
                        );
                    }
                }
            }
        }

        // Si aucune incompatibilité n'a été trouvée, le placement est autorisé
        return true;
    }
}