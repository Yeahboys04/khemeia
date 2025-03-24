<?php

namespace App\Entity;

use App\Repository\IncompatibilityRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * IncompatibilityRequest - Demandes de dérogation pour le stockage de produits incompatibles
 */
#[ORM\Table(name: 'incompatibility_request')]
#[ORM\Entity(repositoryClass: IncompatibilityRequestRepository::class)]
class IncompatibilityRequest
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_requester', referencedColumnName: 'id_user', nullable: true)]
    private ?User $requester = null;

    #[ORM\ManyToOne(targetEntity: Chimicalproduct::class)]
    #[ORM\JoinColumn(name: 'id_product', referencedColumnName: 'id_chimicalProduct', nullable: true)]
    private ?Chimicalproduct $product = null;

    #[ORM\ManyToOne(targetEntity: Shelvingunit::class)]
    #[ORM\JoinColumn(name: 'id_shelvingunit', referencedColumnName: 'id_shelvingUnit', nullable: true)]
    private ?Shelvingunit $shelvingUnit = null;

    #[ORM\Column(name: 'incompatible_with', type: 'text', nullable: true)]
    private ?string $incompatibleWith = null;

    #[ORM\Column(name: 'reason', type: 'text', nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(name: 'is_urgent', type: 'boolean', nullable: true)]
    private ?bool $isUrgent = false;

    #[ORM\Column(name: 'request_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $requestDate = null;

    #[ORM\Column(name: 'status', type: 'string', length: 20, nullable: true)]
    private ?string $status = 'pending';

    #[ORM\Column(name: 'response_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $responseDate = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_responder', referencedColumnName: 'id_user', nullable: true)]
    private ?User $responder = null;

    #[ORM\Column(name: 'response_comment', type: 'text', nullable: true)]
    private ?string $responseComment = null;

    #[ORM\Column(name: 'is_used', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isUsed = false;

    public function __construct()
    {
        $this->requestDate = new \DateTime();
        $this->status = 'pending';
        $this->isUsed = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequester(): ?User
    {
        return $this->requester;
    }

    public function setRequester(?User $requester): self
    {
        $this->requester = $requester;
        return $this;
    }

    public function getProduct(): ?Chimicalproduct
    {
        return $this->product;
    }

    public function setProduct(?Chimicalproduct $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getShelvingUnit(): ?Shelvingunit
    {
        return $this->shelvingUnit;
    }

    public function setShelvingUnit(?Shelvingunit $shelvingUnit): self
    {
        $this->shelvingUnit = $shelvingUnit;
        return $this;
    }

    public function getIncompatibleWith(): ?string
    {
        return $this->incompatibleWith;
    }

    public function setIncompatibleWith(?string $incompatibleWith): self
    {
        $this->incompatibleWith = $incompatibleWith;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getIsUrgent(): ?bool
    {
        return $this->isUrgent;
    }

    public function setIsUrgent(?bool $isUrgent): self
    {
        $this->isUrgent = $isUrgent;
        return $this;
    }

    public function getRequestDate(): ?\DateTimeInterface
    {
        return $this->requestDate;
    }

    public function setRequestDate(?\DateTimeInterface $requestDate): self
    {
        $this->requestDate = $requestDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getResponseDate(): ?\DateTimeInterface
    {
        return $this->responseDate;
    }

    public function setResponseDate(?\DateTimeInterface $responseDate): self
    {
        $this->responseDate = $responseDate;
        return $this;
    }

    public function getResponder(): ?User
    {
        return $this->responder;
    }

    public function setResponder(?User $responder): self
    {
        $this->responder = $responder;
        return $this;
    }

    public function getResponseComment(): ?string
    {
        return $this->responseComment;
    }

    public function setResponseComment(?string $responseComment): self
    {
        $this->responseComment = $responseComment;
        return $this;
    }

    public function getIsUsed(): bool
    {
        return $this->isUsed;
    }

    public function setIsUsed(bool $isUsed): self
    {
        $this->isUsed = $isUsed;
        return $this;
    }
}