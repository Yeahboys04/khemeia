<?php

namespace App\Entity;

use App\Repository\ExternalWithdrawalRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ExternalWithdrawalRequest - Demandes de retrait de produits sur d'autres sites
 */
#[ORM\Table(name: 'external_withdrawal_request')]
#[ORM\Entity(repositoryClass: ExternalWithdrawalRequestRepository::class)]
class ExternalWithdrawalRequest
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'bigint', nullable: false, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_requester', referencedColumnName: 'id_user', nullable: false)]
    private ?User $requester = null;

    #[ORM\ManyToOne(targetEntity: Site::class)]
    #[ORM\JoinColumn(name: 'id_source_site', referencedColumnName: 'id_site', nullable: false)]
    private ?Site $sourceSite = null;

    #[ORM\ManyToOne(targetEntity: Storagecard::class)]
    #[ORM\JoinColumn(name: 'id_source_storagecard', referencedColumnName: 'id_storageCard', nullable: false)]
    private ?Storagecard $sourceStoragecard = null;

    #[ORM\ManyToOne(targetEntity: Site::class)]
    #[ORM\JoinColumn(name: 'id_destination_site', referencedColumnName: 'id_site', nullable: false)]
    private ?Site $destinationSite = null;

    #[ORM\Column(name: 'requested_quantity', type: 'decimal', precision: 11, scale: 2, nullable: false)]
    private ?float $requestedQuantity = null;

    #[ORM\Column(name: 'reason', type: 'text', nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(name: 'is_urgent', type: 'boolean', nullable: false, options: ['default' => false])]
    private ?bool $isUrgent = false;

    #[ORM\Column(name: 'request_date', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $requestDate = null;

    #[ORM\Column(name: 'status', type: 'string', length: 20, nullable: false, options: ['default' => 'pending'])]
    private ?string $status = 'pending';

    #[ORM\Column(name: 'response_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $responseDate = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_responder', referencedColumnName: 'id_user', nullable: true)]
    private ?User $responder = null;

    #[ORM\Column(name: 'response_comment', type: 'text', nullable: true)]
    private ?string $responseComment = null;

    #[ORM\Column(name: 'is_completed', type: 'boolean', nullable: false, options: ['default' => false])]
    private ?bool $isCompleted = false;

    #[ORM\ManyToOne(targetEntity: Storagecard::class)]
    #[ORM\JoinColumn(name: 'id_destination_storagecard', referencedColumnName: 'id_storageCard', nullable: true)]
    private ?Storagecard $destinationStoragecard = null;

    public function __construct()
    {
        $this->requestDate = new \DateTime();
        $this->status = 'pending';
        $this->isCompleted = false;
        $this->isUrgent = false;
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

    public function getSourceSite(): ?Site
    {
        return $this->sourceSite;
    }

    public function setSourceSite(?Site $sourceSite): self
    {
        $this->sourceSite = $sourceSite;
        return $this;
    }

    public function getSourceStoragecard(): ?Storagecard
    {
        return $this->sourceStoragecard;
    }

    public function setSourceStoragecard(?Storagecard $sourceStoragecard): self
    {
        $this->sourceStoragecard = $sourceStoragecard;
        return $this;
    }

    public function getDestinationSite(): ?Site
    {
        return $this->destinationSite;
    }

    public function setDestinationSite(?Site $destinationSite): self
    {
        $this->destinationSite = $destinationSite;
        return $this;
    }

    public function getRequestedQuantity(): ?float
    {
        return $this->requestedQuantity;
    }

    public function setRequestedQuantity(?float $requestedQuantity): self
    {
        $this->requestedQuantity = $requestedQuantity;
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

    public function getIsCompleted(): ?bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(?bool $isCompleted): self
    {
        $this->isCompleted = $isCompleted;
        return $this;
    }

    public function getDestinationStoragecard(): ?Storagecard
    {
        return $this->destinationStoragecard;
    }

    public function setDestinationStoragecard(?Storagecard $destinationStoragecard): self
    {
        $this->destinationStoragecard = $destinationStoragecard;
        return $this;
    }
}