<?php

namespace App\Repository;

use App\Entity\Chimicalproduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChimicalproductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chimicalproduct::class);
    }

    /**
     * Recherche des produits chimiques par nom
     *
     * @param string $term Terme de recherche
     * @param int $limit Nombre maximum de résultats
     * @return Chimicalproduct[]
     */
    public function searchByName(string $term, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nameChimicalproduct LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.nameChimicalproduct', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un produit chimique existe avec ce nom exact
     *
     * @param string $name Nom du produit
     * @return bool
     */
    public function existsByName(string $name): bool
    {
        return null !== $this->findOneBy(['nameChimicalproduct' => $name]);
    }
}