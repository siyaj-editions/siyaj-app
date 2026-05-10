<?php

namespace App\Entity;

use App\Enum\OrderSend;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', enumType: OrderStatus::class)]
    private ?OrderStatus $status = OrderStatus::PENDING;

    #[ORM\Column]
    private ?int $totalCents = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $itemsSubtotalCents = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $shippingCostCents = 0;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $shippingZone = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $shippingZoneLabel = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $shippingMethod = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $shippingMethodLabel = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $shippingDelayLabel = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = 'EUR';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(type: 'string', enumType: OrderSend::class, options: ['default' => OrderSend::PROCESSING->value])]
    private OrderSend $sendStatus = OrderSend::PROCESSING;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trackingNumber = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidNotificationSentAt = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Address $shippingAddress = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Address $billingAddress = null;

    #[ORM\Column]
    private bool $billingSameAsShipping = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', orphanRemoval: true, cascade: ['persist'])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->status = OrderStatus::PENDING;
        $this->sendStatus = OrderSend::PROCESSING;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): ?OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalCents(): ?int
    {
        return $this->totalCents;
    }

    public function setTotalCents(int $totalCents): static
    {
        $this->totalCents = $totalCents;

        return $this;
    }

    public function getTotalEuros(): float
    {
        return $this->totalCents / 100;
    }

    public function getItemsSubtotalCents(): int
    {
        return $this->itemsSubtotalCents;
    }

    public function setItemsSubtotalCents(int $itemsSubtotalCents): static
    {
        $this->itemsSubtotalCents = $itemsSubtotalCents;

        return $this;
    }

    public function getItemsSubtotalEuros(): float
    {
        return $this->itemsSubtotalCents / 100;
    }

    public function getShippingCostCents(): int
    {
        return $this->shippingCostCents;
    }

    public function setShippingCostCents(int $shippingCostCents): static
    {
        $this->shippingCostCents = $shippingCostCents;

        return $this;
    }

    public function getShippingCostEuros(): float
    {
        return $this->shippingCostCents / 100;
    }

    public function getShippingZone(): ?string
    {
        return $this->shippingZone;
    }

    public function setShippingZone(?string $shippingZone): static
    {
        $this->shippingZone = $shippingZone;

        return $this;
    }

    public function getShippingZoneLabel(): ?string
    {
        return $this->shippingZoneLabel;
    }

    public function setShippingZoneLabel(?string $shippingZoneLabel): static
    {
        $this->shippingZoneLabel = $shippingZoneLabel;

        return $this;
    }

    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(?string $shippingMethod): static
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    public function getShippingMethodLabel(): ?string
    {
        return $this->shippingMethodLabel;
    }

    public function setShippingMethodLabel(?string $shippingMethodLabel): static
    {
        $this->shippingMethodLabel = $shippingMethodLabel;

        return $this;
    }

    public function getShippingDelayLabel(): ?string
    {
        return $this->shippingDelayLabel;
    }

    public function setShippingDelayLabel(?string $shippingDelayLabel): static
    {
        $this->shippingDelayLabel = $shippingDelayLabel;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): static
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getSendStatus(): OrderSend
    {
        return $this->sendStatus;
    }

    public function setSendStatus(OrderSend $sendStatus): static
    {
        $this->sendStatus = $sendStatus;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): static
    {
        $normalizedTrackingNumber = $trackingNumber !== null ? trim($trackingNumber) : null;
        $this->trackingNumber = $normalizedTrackingNumber !== '' ? $normalizedTrackingNumber : null;

        return $this;
    }

    public function getPaidNotificationSentAt(): ?\DateTimeImmutable
    {
        return $this->paidNotificationSentAt;
    }

    public function setPaidNotificationSentAt(?\DateTimeImmutable $paidNotificationSentAt): static
    {
        $this->paidNotificationSentAt = $paidNotificationSentAt;

        return $this;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(Address $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(Address $billingAddress): static
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function isBillingSameAsShipping(): bool
    {
        return $this->billingSameAsShipping;
    }

    public function setBillingSameAsShipping(bool $billingSameAsShipping): static
    {
        $this->billingSameAsShipping = $billingSameAsShipping;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    public function calculateTotal(): void
    {
        $itemsSubtotal = 0;
        foreach ($this->orderItems as $item) {
            $itemsSubtotal += $item->getPriceSnapshot() * $item->getQuantity();
        }
        $this->itemsSubtotalCents = $itemsSubtotal;
        $this->totalCents = $itemsSubtotal + $this->shippingCostCents;
    }

    public function getReference(): string
    {
        $year = $this->createdAt?->format('Y') ?? date('Y');
        return 'ORD-' . $year . '-' . str_pad((string)$this->id, 5, '0', STR_PAD_LEFT);
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID;
    }

    public function isSent(): bool
    {
        return $this->sendStatus === OrderSend::SENT;
    }

    public function isReceived(): bool
    {
        return $this->sendStatus === OrderSend::RECEIVED;
    }
}
