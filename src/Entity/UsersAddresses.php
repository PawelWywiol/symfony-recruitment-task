<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UsersAddressesRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

use function in_array;

#[ORM\Entity(repositoryClass: UsersAddressesRepository::class)]
#[ORM\Table(name: 'users_addresses', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'user_address_type_valid_from', columns: ['user_id', 'address_type', 'valid_from']),
])]
#[ORM\HasLifecycleCallbacks]
class UsersAddresses
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Users $user = null;

    #[ORM\Column(length: 7)]
    private ?string $addressType = null;

    #[ORM\Column(name: 'valid_from', type: Types::DATETIME_MUTABLE)]
    private DateTime $validFrom;

    #[ORM\Column(length: 6)]
    private ?string $postCode = null;

    #[ORM\Column(length: 60)]
    private ?string $city = null;

    #[ORM\Column(length: 3)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 100)]
    private ?string $street = null;

    #[ORM\Column(length: 60)]
    private ?string $buildingNumber = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private DateTime $updatedAt;

    public function __construct()
    {
        $this->validFrom = new DateTime();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAddressType(): ?string
    {
        return $this->addressType;
    }

    public function setAddressType(string $addressType): static
    {
        if (!in_array($addressType, ['HOME', 'INVOICE', 'POST', 'WORK'], true)) {
            throw new InvalidArgumentException('Invalid address type');
        }

        $this->addressType = $addressType;

        return $this;
    }

    public function getValidFrom(): DateTime
    {
        return $this->validFrom;
    }

    public function setValidFrom(DateTime $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    public function setPostCode(string $postCode): static
    {
        $this->postCode = $postCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getBuildingNumber(): ?string
    {
        return $this->buildingNumber;
    }

    public function setBuildingNumber(string $buildingNumber): static
    {
        $this->buildingNumber = $buildingNumber;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
