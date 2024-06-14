<?php

namespace App\Entity;

use App\Repository\MailingHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailingHistoryRepository::class)]
class MailingHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mailingHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $member = null;

    #[ORM\Column(length: 255)]
    private ?string $recipient = null;

    #[ORM\Column]
    private array $recipientFilters = [];

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $sender = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textBody = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $htmlBody = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $watchedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getRecipientFilters(): array
    {
        return $this->recipientFilters;
    }

    public function setRecipientFilters(array $recipientFilters): static
    {
        $this->recipientFilters = $recipientFilters;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTextBody(): ?string
    {
        return $this->textBody;
    }

    public function setTextBody(string $textBody): static
    {
        $this->textBody = $textBody;

        return $this;
    }

    public function getHtmlBody(): ?string
    {
        return $this->htmlBody;
    }

    public function setHtmlBody(string $htmlBody): static
    {
        $this->htmlBody = $htmlBody;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getWatchedAt(): ?\DateTimeImmutable
    {
        return $this->watchedAt;
    }

    public function setWatchedAt(?\DateTimeImmutable $watchedAt): static
    {
        $this->watchedAt = $watchedAt;

        return $this;
    }
}
