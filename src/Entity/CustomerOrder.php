<?php

namespace App\Entity;

use App\Repository\CustomerOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerOrderRepository::class)]
class CustomerOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $customerFirstName = null;

    #[ORM\Column(length: 100)]
    private ?string $customerLastname = null;

    #[ORM\Column(length: 180)]
    private ?string $customerEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $customerPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryAddress = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $deliveryPostalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $deliveryCity = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $deliveryTime = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $deliveryDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryPlace = null;

    #[ORM\Column]
    private ?int $personCount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $menuPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $deliveryPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $discountAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $totalPrice = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancelReason = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $contactMode = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'customerOrders')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'customerOrders')]
    private ?Menu $menu = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerFirstName(): ?string
    {
        return $this->customerFirstName;
    }

    public function setCustomerFirstName(string $customerFirstName): static
    {
        $this->customerFirstName = $customerFirstName;

        return $this;
    }

    public function getCustomerLastname(): ?string
    {
        return $this->customerLastname;
    }

    public function setCustomerLastname(string $customerLastname): static
    {
        $this->customerLastname = $customerLastname;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(?string $customerPhone): static
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }

   public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?string $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getDeliveryPostalCode(): ?string
    {
        return $this->deliveryPostalCode;
    }

    public function setDeliveryPostalCode(?string $deliveryPostalCode): static
    {
        $this->deliveryPostalCode = $deliveryPostalCode;

        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(?string $deliveryCity): static
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }

    public function getDeliveryTime(): ?\DateTimeImmutable
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(\DateTimeImmutable $deliveryTime): static
    {
        $this->deliveryTime = $deliveryTime;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTimeImmutable $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getDeliveryPlace(): ?string
    {
        return $this->deliveryPlace;
    }

    public function setDeliveryPlace(?string $deliveryPlace): static
    {
        $this->deliveryPlace = $deliveryPlace;

        return $this;
    }

    public function getPersonCount(): ?int
    {
        return $this->personCount;
    }

    public function setPersonCount(int $personCount): static
    {
        $this->personCount = $personCount;

        return $this;
    }

    public function getMenuPrice(): ?string
    {
        return $this->menuPrice;
    }

    public function setMenuPrice(?string $menuPrice): static
    {
        $this->menuPrice = $menuPrice;

        return $this;
    }

    public function getDeliveryPrice(): ?string
    {
        return $this->deliveryPrice;
    }

    public function setDeliveryPrice(?string $deliveryPrice): static
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    public function getDiscountAmount(): ?string
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(?string $discountAmount): static
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    public function setCancelReason(?string $cancelReason): static
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    public function getContactMode(): ?string
    {
        return $this->contactMode;
    }

    public function setContactMode(?string $contactMode): static
    {
        $this->contactMode = $contactMode;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }
}
