<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Fonctions personnalisées de gestion des utilisateurs dans la base de données
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Charge un utilisateur en fonction de son login
     *
     * @param string $username
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername(string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.idStatus = :query')
            ->setParameter('query', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Deprecated method - replacement for loadUserByUsername
     *
     * @param string $identifier
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->loadUserByUsername($identifier);
    }

    /**
     * Charge la liste des administrateurs
     *
     * @return User[]
     */
    public function loadAdministrators(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.idStatus = 4')
            ->getQuery()
            ->getResult();
    }

    /**
     * Charge la liste des responsables d'un site
     *
     * @param Site|int $site
     * @return User[]
     */
    public function loadSupervisors($site): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.idStatus = 3')
            ->andWhere('u.idSite = :query')
            ->setParameter('query', $site)
            ->getQuery()
            ->getResult();
    }
}