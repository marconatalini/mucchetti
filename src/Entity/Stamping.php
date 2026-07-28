<?php

namespace App\Entity;

use App\Repository\StampingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: StampingRepository::class)]
class Stamping
{

    public const array TYPES = ['Income', 'Outcome'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stampings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $employee = null;

    #[ORM\Column]
    #[Assert\LessThan('now Europe/Rome', message: 'date.past.needed')]
    private ?\DateTimeImmutable $missedAt = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(length: 30)]
    private ?string $stampingType = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $evaluatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $declaredAt = null;

    public function __construct()
    {
        $this->declaredAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployee(): ?User
    {
        return $this->employee;
    }

    public function setEmployee(?User $employee): static
    {
        $this->employee = $employee;

        return $this;
    }


    public function getMissedAt(): ?\DateTimeImmutable
    {
        return $this->missedAt;
    }

    public function setMissedAt(\DateTimeImmutable $missedAt): static
    {
        $this->missedAt = $missedAt;

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

    public function getStampingType(): ?string
    {
        return $this->stampingType;
    }

    public function setStampingType(string $stampingType): static
    {
        $this->stampingType = $stampingType;

        return $this;
    }

    public function getEvaluatedAt(): ?\DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function setEvaluatedAt(?\DateTimeImmutable $evaluatedAt): static
    {
        $this->evaluatedAt = $evaluatedAt;

        return $this;
    }

    public function getDeclaredAt(): ?\DateTimeImmutable
    {
        return $this->declaredAt;
    }

    public function setDeclaredAt(\DateTimeImmutable $declaredAt): static
    {
        $this->declaredAt = $declaredAt;

        return $this;
    }
}
