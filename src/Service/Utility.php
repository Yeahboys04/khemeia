<?php

namespace App\Service;

use App\Entity\Chimicalproduct;
use App\Entity\Shelvingunit;
use App\Entity\Storagecard;
use Doctrine\ORM\EntityManager;
use LogicException;

class Utility
{
    
    
    public function movedIsAuthorised (Shelvingunit $idShelvingunit,
                                       Chimicalproduct $chimicalproduct,
                                       EntityManager $entityManager)
    {
        //Je récupère la liste des types de mon produit
        $types = $chimicalproduct->getIdType();
        //Je récupère la liste des types avec lesquels mon produit est incompatible
        $incompatibleTypes = null;
        foreach ($types as $type) {
            $collectionTypes = $type->getIdType2();
            foreach ($collectionTypes as $collectionType){
                $incompatibleTypes [] = $collectionType->getIdType();
            }
        }
        //Je récupère les produits présents sur l'étagère où je tente de placer mon produit
        $repositoryStoragecard = $entityManager
            ->getRepository(Storagecard::class);
        
        $storagecards = $repositoryStoragecard->loadStorageCardByShelvingunit($idShelvingunit);
        //Pour chaque produit présent, je récupère la liste des types de ces produits
        $compatibleTypes = null;
        foreach ($storagecards as $storagecard){
            $shelvingunitTypes = $storagecard->getIdChimicalproduct()->getIdType();
            foreach ($shelvingunitTypes as $shelvingunitType) {
                $compatibleTypes [] = $shelvingunitType->getIdType();
            }
            //S'il n'y a pas de types dans les produits présents, on valide
            if(!empty($compatibleTypes) && !empty($incompatibleTypes)){
                //Pour chaque type, je compare les deux tableaux
                $result = array_intersect($incompatibleTypes, $compatibleTypes);
                //S'il y a une valeur de retour dans les résultats, 
                //Alors le produit ne peux pas aller sur cette étagère
                if (!empty($result)) {
                    throw new LogicException("Attention, vous ne pouvez pas ranger le produit '"
                        . $chimicalproduct->getNameChimicalproduct() . 
                        "' sur l'étagère '"
                        . $idShelvingunit->getNameShelvingunit()
                        ."' car ce produit est incompatible avec ceux déja présent." 
                        ." Merci de le ranger ailleurs.");
                }
            }
        }
    }
}