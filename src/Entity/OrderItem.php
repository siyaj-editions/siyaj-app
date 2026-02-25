<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    #[ORM\Column(length: 255)]
    private ?string $titleSnapshot = null;

    #[ORM\Column]
    private ?int $priceSnapshot = null;

    #[ORM\Column]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getTitleSnapshot(): ?string
    {
        return $this->titleSnapshot;
    }

    public function setTitleSnapshot(string $titleSnapshot): static
    {
        $this->titleSnapshot = $titleSnapshot;

        return $this;
    }

    public function getPriceSnapshot(): ?int
    {
        return $this->priceSnapshot;
    }

    public function setPriceSnapshot(int $priceSnapshot): static
    {
        $this->priceSnapshot = $priceSnapshot;

        return $this;
    }

    public function getPriceSnapshotEuros(): float
    {
        return $this->priceSnapshot / 100;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTotal(): int
    {
        return $this->priceSnapshot * $this->quantity;
    }

    public function getTotalEuros(): float
    {
        return $this->getTotal() / 100;
    }
}
