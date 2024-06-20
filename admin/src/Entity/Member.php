<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\UniqueConstraint(name: "shortName", columns: ["short_name"])]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(length: 255)]
    private ?string $emailAddress = null;

    /**
     * @var Collection<int, SeasonTicket>
     */
    #[ORM\OneToMany(targetEntity: SeasonTicket::class, mappedBy: 'member')]
    private Collection $seasonTickets;

    #[ORM\Column(type: Types::JSON)]
    private array $tags;

    #[ORM\Column(nullable: true)]
    private ?bool $isDisabled = null;

    /**
     * @var Collection<int, MailingHistory>
     */
    #[ORM\OneToMany(targetEntity: MailingHistory::class, mappedBy: 'member')]
    private Collection $mailingHistories;

    public function __construct()
    {
        $this->seasonTickets = new ArrayCollection();
        $this->mailingHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): static
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return Collection<int, SeasonTicket>
     */
    public function getSeasonTickets(): Collection
    {
        return $this->seasonTickets;
    }

    public function addSeasonTicket(SeasonTicket $seasonTicket): static
    {
        if (!$this->seasonTickets->contains($seasonTicket)) {
            $this->seasonTickets->add($seasonTicket);
            $seasonTicket->setMember($this);
        }

        return $this;
    }

    public function removeSeasonTicket(SeasonTicket $seasonTicket): static
    {
        if ($this->seasonTickets->removeElement($seasonTicket)) {
            // set the owning side to null (unless already changed)
            if ($seasonTicket->getMember() === $this) {
                $seasonTicket->setMember(null);
            }
        }

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->isDisabled;
    }

    public function setDisabled(bool $isDisabled): static
    {
        $this->isDisabled = $isDisabled;

        return $this;
    }

    /**
     * @return Collection<int, MailingHistory>
     */
    public function getMailingHistories(): Collection
    {
        return $this->mailingHistories;
    }

    public function addMailingHistory(MailingHistory $mailingHistory): static
    {
        if (!$this->mailingHistories->contains($mailingHistory)) {
            $this->mailingHistories->add($mailingHistory);
            $mailingHistory->setMember($this);
        }

        return $this;
    }

    public function removeMailingHistory(MailingHistory $mailingHistory): static
    {
        if ($this->mailingHistories->removeElement($mailingHistory)) {
            // set the owning side to null (unless already changed)
            if ($mailingHistory->getMember() === $this) {
                $mailingHistory->setMember(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return sprintf("%s %s", $this->getFirstName(), $this->getLastName());
    }
}
