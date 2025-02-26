<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité définissant les utilisateurs et les paramètres de la base de données
 */
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'id_user', columns: ['id_user'])]
#[ORM\UniqueConstraint(name: 'mail', columns: ['mail'])]
#[ORM\UniqueConstraint(name: 'username', columns: ['username'])]
#[ORM\Index(name: 'FK_user_status', columns: ['id_status'])]
#[ORM\Index(name: 'FK_user_site', columns: ['id_site'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(name: 'id_user', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $idUser = null;

    #[Assert\NotBlank(message: "Le login '{{ value }}' n'est pas un login valide.")]
    #[ORM\Column(name: 'username', type: 'string', length: 250, nullable: false)]
    private ?string $username = null;

    #[Assert\NotBlank(message: "Le nom prénom '{{ value }}' n'est pas valide.")]
    #[ORM\Column(name: 'fullName', type: 'string', length: 250, nullable: false)]
    private ?string $fullname = null;

    #[Assert\NotBlank]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas un email valide.")]
    #[ORM\Column(name: 'mail', type: 'string', length: 250, nullable: false)]
    private ?string $mail = null;

    #[Assert\NotBlank(allowNull: true)]
    #[ORM\Column(name: 'password', type: 'string', length: 250, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(name: 'endRightDate', type: 'date', nullable: true, options: ['default' => null])]
    private ?\DateTimeInterface $endrightdate = null;

    #[ORM\ManyToOne(targetEntity: Site::class)]
    #[ORM\JoinColumn(name: 'id_site', referencedColumnName: 'id_site')]
    private ?Site $idSite = null;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    #[ORM\JoinColumn(name: 'id_status', referencedColumnName: 'id_status')]
    private ?Status $idStatus = null;

    #[ORM\ManyToMany(targetEntity: Storagecard::class, inversedBy: 'idUser')]
    #[ORM\JoinTable(name: 'tracability')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user')]
    #[ORM\InverseJoinColumn(name: 'id_storageCard', referencedColumnName: 'id_storageCard')]
    private Collection $idStoragecard;

    /**
     * @var array<string>
     */
    private array $roles = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->idStoragecard = new ArrayCollection();
    }

    public function getIdUser(): ?string
    {
        return $this->idUser;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @deprecated Utilisez la nouvelle interface PasswordAuthenticatedUserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = $this->getIdStatus()->getRoleStatus();
        $roles[] = 'ROLE_VISITOR';
        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles comme des tokens
    }

    public function getEndrightdate(): ?\DateTimeInterface
    {
        return $this->endrightdate;
    }

    public function setEndrightdate(?\DateTimeInterface $endrightdate): self
    {
        $this->endrightdate = $endrightdate;

        return $this;
    }

    public function getIdSite(): ?Site
    {
        return $this->idSite;
    }

    public function setIdSite(?Site $idSite): self
    {
        $this->idSite = $idSite;

        return $this;
    }

    public function getIdStatus(): ?Status
    {
        return $this->idStatus;
    }

    public function setIdStatus(?Status $idStatus): self
    {
        $this->idStatus = $idStatus;

        return $this;
    }

    /**
     * @return Collection<int, Storagecard>
     */
    public function getIdStoragecard(): Collection
    {
        return $this->idStoragecard;
    }

    public function addIdStoragecard(Storagecard $idStoragecard): self
    {
        if (!$this->idStoragecard->contains($idStoragecard)) {
            $this->idStoragecard->add($idStoragecard);
        }

        return $this;
    }

    public function removeIdStoragecard(Storagecard $idStoragecard): self
    {
        $this->idStoragecard->removeElement($idStoragecard);

        return $this;
    }

    public function __toString(): string
    {
        return $this->username;
    }
}