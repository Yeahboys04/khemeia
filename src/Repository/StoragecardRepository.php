<?php

namespace App\Repository;

use App\Entity\Storagecard;
use App\Entity\Shelvingunit;
use App\Entity\Cupboard;
use App\Entity\Stock;
use App\Entity\Site;
use App\Entity\Chimicalproduct;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StoragecardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Storagecard::class);
    }

    /**
     * Load storage cards by site and chemical product
     *
     * @param Site|int $site The site entity or its ID
     * @param Chimicalproduct|int $idChemicalproduct The chemical product entity or its ID
     * @return Storagecard[]
     */
    public function loadStorageCardBySite($site, $idChemicalproduct): array
    {
        return $this->createQueryBuilder('sc')
            ->innerJoin('App\Entity\Shelvingunit', 'sh', 'WITH', 'sc.idShelvingunit = sh.idShelvingunit')
            ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = sh.idCupboard')
            ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
            ->where('st.idSite = :querysite')
            ->andWhere('sc.idChimicalproduct = :querychimicalproduct')
            ->setParameters([
                'querysite' => $site,
                'querychimicalproduct' => $idChemicalproduct
            ])
            ->getQuery()
            ->getResult();
    }


    public function loadStorageCardsByCAS($casnumber): array
    {
        return $this->createQueryBuilder('sc')
            ->innerJoin(Chimicalproduct::class, 'cp', 'WITH', 'sc.idChimicalproduct = cp.idChimicalproduct')
            ->where('cp.casnumber = :querycas')
            ->andWhere('sc.isarchived = false')
            ->setParameter('querycas', $casnumber)
            ->getQuery()
            ->getResult();
    }

    /**
     * Load storage cards by site with sufficient stock and valid expiration date
     *
     * @param Site|int $site The site entity or its ID
     * @return Storagecard[]
     */
    public function loadStorageCardsBySite($site): array
    {
        return $this->createQueryBuilder('sc')
            ->innerJoin('App\Entity\Shelvingunit', 'sh', 'WITH', 'sc.idShelvingunit = sh.idShelvingunit')
            ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = sh.idCupboard')
            ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
            ->where('st.idSite = :querysite')
            ->andWhere('(sc.stockquantity > (0.10*sc.capacity)) OR sc.stockquantity IS NULL')
            ->andWhere('sc.expirationdate >= :querydate OR sc.expirationdate IS NULL')
            ->andWhere('sc.isarchived = false')
            ->setParameters([
                'querysite' => $site,
                'querydate' => new DateTime()
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Load storage cards by site with empty stock
     *
     * @param Site|int $site The site entity or its ID
     * @return Storagecard[]
     */
    public function loadStorageCardsBySiteAndEmptyStock($site): array
    {
        return $this->createQueryBuilder('sc')
            ->innerJoin('App\Entity\Shelvingunit', 'sh', 'WITH', 'sc.idShelvingunit = sh.idShelvingunit')
            ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = sh.idCupboard')
            ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
            ->where('st.idSite = :querysite')
            ->andWhere('sc.stockquantity = 0')
            ->andWhere('sc.isarchived = false')
            ->setParameter('querysite', $site)
            ->getQuery()
            ->getResult();
    }

    /**
     * Load storage cards by site with low stock or expired products
     *
     * @param Site|int $site The site entity or its ID
     * @return Storagecard[]
     */
    public function loadStorageCardsBySiteAndExpirationDate($site): array
    {
        return $this->createQueryBuilder('sc')
            ->innerJoin('App\Entity\Shelvingunit', 'sh', 'WITH', 'sc.idShelvingunit = sh.idShelvingunit')
            ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = sh.idCupboard')
            ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
            ->where('st.idSite = :querysite')
            ->andWhere('(sc.stockquantity <= (0.10*sc.capacity) AND sc.stockquantity != 0) OR sc.expirationdate < :querydate')
            ->andWhere('sc.isarchived = false')
            ->setParameters([
                'querysite' => $site,
                'querydate' => new DateTime()
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Load storage card data by site for CSV export
     *
     * @param Site|int $site The site entity or its ID
     * @return \Doctrine\ORM\Query
     */
    public function loadStorageCardBySiteForCSV($site): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('sc')
            ->select('cp.casnumber, 1, 1, sc.stockquantity, c.nameCupboard, sh.nameShelvingunit')
            ->innerJoin('App\Entity\Chimicalproduct', 'cp', 'WITH', 'sc.idChimicalproduct = cp.idChimicalproduct')
            ->innerJoin('App\Entity\Shelvingunit', 'sh', 'WITH', 'sc.idShelvingunit = sh.idShelvingunit')
            ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = sh.idCupboard')
            ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
            ->where('st.idSite = :querysite')
            ->andWhere('sc.isarchived = false')
            ->andWhere('cp.casnumber IS NOT NULL')
            ->setParameter('querysite', $site)
            ->getQuery();
    }

    /**
     * Load storage cards by site for PDF export
     *
     * @param Site|int $site The site entity or its ID
     * @return Storagecard[]
     */
    public function loadStorageCardBySiteForPDF($site): array
    {
        return $this->createQueryBuilder('sc')
            ->innerJoin('App\Entity\Shelvingunit', 'sh', 'WITH', 'sc.idShelvingunit = sh.idShelvingunit')
            ->innerJoin('App\Entity\Cupboard', 'c', 'WITH', 'c.idCupboard = sh.idCupboard')
            ->innerJoin('App\Entity\Stock', 'st', 'WITH', 'c.idStock = st.idStock')
            ->where('st.idSite = :querysite')
            ->andWhere('sc.isarchived = false')
            ->setParameter('querysite', $site)
            ->getQuery()
            ->getResult();
    }


    /**
     * Load storage cards by shelving unit
     *
     * @param Shelvingunit|int $idShelvingunit The shelving unit entity or its ID
     * @return Storagecard[]
     */
    public function loadStorageCardByShelvingunit($idShelvingunit): array
    {
        return $this->createQueryBuilder('sc')
            ->where('sc.idShelvingunit = :query')
            ->andWhere('sc.isarchived = false')
            ->setParameter('query', $idShelvingunit)
            ->getQuery()
            ->getResult();
    }

}