<?php

namespace App\Entity;

use App\Repository\SeasonTicketRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonTicketRepository::class)]
class SeasonTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $season = null;

    #[ORM\Column(length: 10)]
    private ?string $block = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(nullable: true)]
    private ?string $row = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $seat = null;

    #[ORM\ManyToOne(inversedBy: 'seasonTickets')]
    private ?Member $member = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getBlock(): ?string
    {
        return $this->block;
    }

    public function setBlock(string $block): static
    {
        $this->block = $block;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getRow(): ?string
    {
        return $this->row;
    }

    public function setRow(?string $row): static
    {
        $this->row = $row;

        return $this;
    }

    public function getSeat(): ?string
    {
        return $this->seat;
    }

    public function setSeat(?string $seat): static
    {
        $this->seat = $seat;

        return $this;
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
}
