<?php

namespace App\Service;

use App\Entity\Chimicalproduct;
use App\Entity\Controlbytype;
use App\Entity\Shelvingunit;
use App\Entity\Storagecard;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class Utility
{
    /**
     * Vérifie si un produit peut être déplacé à un emplacement donné
     *
     * @param Shelvingunit $idShelvingunit L'emplacement cible
     * @param Chimicalproduct $chimicalproduct Le produit à déplacer
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités
     * @param bool $overrideIncompatibility Si true, ignore les vérifications d'incompatibilité (admin uniquement)
     * @return bool True si le déplacement est autorisé
     * @throws LogicException Si le déplacement n'est pas autorisé
     */
    public function movedIsAuthorised(
        Shelvingunit $idShelvingunit,
        Chimicalproduct $chimicalproduct,
        EntityManagerInterface $entityManager,
        bool $overrideIncompatibility = false
    ): bool {
        // Si l'override est activé, on autorise le déplacement sans vérification
        if ($overrideIncompatibility === true) {
            return true;
        }

        $repositoryStoragecard = $entityManager->getRepository(Storagecard::class);
        $storagecards = $repositoryStoragecard->loadStorageCardByShelvingunit($idShelvingunit);
        $repositoryControlByType = $entityManager->getRepository(Controlbytype::class);

        // Récupérer les types du produit qu'on veut déplacer
        $types1 = $chimicalproduct->getIdType();

        foreach ($storagecards as $storagecard) {
            // Récupérer les types du produit déjà présent
            $types2 = $storagecard->getIdChimicalproduct()->getIdType();

            // Vérifier les combinaisons de types pour incompatibilité
            foreach ($types1 as $type1) {
                foreach ($types2 as $type2) {
                    $control = $repositoryControlByType->findOneBy([
                        'idType1' => $type1->getIdType(),
                        'idType2' => $type2->getIdType()
                    ]);

                    if ($control == null) {
                        $control = $repositoryControlByType->findOneBy([
                            'idType1' => $type2->getIdType(),
                            'idType2' => $type1->getIdType()
                        ]);
                    }

                    // Si une règle d'incompatibilité est trouvée
                    if ($control != null && !$control->getIscompatible()) {
                        throw new LogicException(
                            'Le produit ' . $chimicalproduct->getNameChimicalproduct() .
                            ' de type ' . $type1->getNameType() .
                            ' n\'est pas compatible avec le produit ' .
                            $storagecard->getIdChimicalproduct()->getNameChimicalproduct() .
                            ' de type ' . $type2->getNameType() .
                            ' déjà présent à cet emplacement.');
                    }
                }
            }
        }

        return true;
    }
}