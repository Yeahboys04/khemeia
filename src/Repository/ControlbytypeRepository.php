<?php

namespace App\Repository;

use App\Entity\Controlbytype;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ControlbytypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Controlbytype::class);
    }

    /**
     * Trouve une règle de compatibilité entre deux types de produits
     *
     * @param int $idType1 ID du premier type
     * @param int $idType2 ID du deuxième type
     * @return Controlbytype|null Renvoie la règle si elle existe, null sinon
     */
    public function findCompatibilityRule($idType1, $idType2): ?Controlbytype
    {
        return $this->createQueryBuilder('c')
            ->where('c.idType1 = :idType1 AND c.idType2 = :idType2')
            ->setParameter('idType1', $idType1)
            ->setParameter('idType2', $idType2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si deux types sont compatibles
     * Essaie de trouver une règle dans les deux sens (type1→type2 et type2→type1)
     *
     * @param int $idType1 ID du premier type
     * @param int $idType2 ID du deuxième type
     * @return bool|null true si compatible, false si incompatible, null si aucune règle trouvée
     */
    public function areTypesCompatible($idType1, $idType2): ?bool
    {
        // Essai dans le premier sens
        $rule = $this->findCompatibilityRule($idType1, $idType2);

        // Si aucune règle trouvée, essai dans l'autre sens
        if (!$rule) {
            $rule = $this->findCompatibilityRule($idType2, $idType1);
        }

        // Si une règle existe, renvoie sa valeur de compatibilité
        if ($rule) {
            return $rule->getIscompatible();
        }

        // Aucune règle trouvée
        return null;
    }
}