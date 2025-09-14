<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $badge = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $employeeCode = null;

    #[ORM\Column(length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    private ?string $lastName = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'staffMembers')]
    private ?self $parentUser = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentUser')]
    private Collection $staffMembers;

    /**
     * @var Collection<int, Permit>
     */
    #[ORM\OneToMany(targetEntity: Permit::class, mappedBy: 'employee')]
    private Collection $permits;

    public function __construct()
    {
        $this->staffMembers = new ArrayCollection();
        $this->permits = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function setBadge(?string $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getEmployeeCode(): ?string
    {
        return $this->employeeCode;
    }

    public function setEmployeeCode(?string $employeeCode): static
    {
        $this->employeeCode = $employeeCode;

        return $this;
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

    public function getParentUser(): ?self
    {
        return $this->parentUser;
    }

    public function setParentUser(?self $parentUser): static
    {
        $this->parentUser = $parentUser;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getStaffMembers(): Collection
    {
        return $this->staffMembers;
    }

    public function addStaffMember(self $staffMember): static
    {
        if (!$this->staffMembers->contains($staffMember)) {
            $this->staffMembers->add($staffMember);
            $staffMember->setParentUser($this);
        }

        return $this;
    }

    public function removeStaffMember(self $staffMember): static
    {
        if ($this->staffMembers->removeElement($staffMember)) {
            // set the owning side to null (unless already changed)
            if ($staffMember->getParentUser() === $this) {
                $staffMember->setParentUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Permit>
     */
    public function getPermits(): Collection
    {
        return $this->permits;
    }

    public function addPermit(Permit $permit): static
    {
        if (!$this->permits->contains($permit)) {
            $this->permits->add($permit);
            $permit->setEmployee($this);
        }

        return $this;
    }

    public function removePermit(Permit $permit): static
    {
        if ($this->permits->removeElement($permit)) {
            // set the owning side to null (unless already changed)
            if ($permit->getEmployee() === $this) {
                $permit->setEmployee(null);
            }
        }

        return $this;
    }
}
