<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $initials = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 8, options: ['default' => 'ACTIVE'])]
    private string $status = 'ACTIVE';

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    private DateTime $updatedAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UsersAddresses::class, orphanRemoval: true)]
    private Collection $addresses;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->addresses = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
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

    public function getInitials(): ?string
    {
        return $this->initials;
    }

    public function setInitials(?string $initials): static
    {
        $this->initials = $initials;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    /**
     * @return Collection<int, UsersAddresses>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(UsersAddresses $address): static
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }

        return $this;
    }

    public function removeAddress(UsersAddresses $address): static
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }

        return $this;
    }
}
