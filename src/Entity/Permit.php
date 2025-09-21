<?php

namespace App\Entity;

use App\Repository\PermitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: PermitRepository::class)]
class Permit
{
    public const array TYPES = ['Permesso', 'Permesso non retribuito', 'Ferie', 'Legge 104'];

    public const STATUS_START = 'start';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REGISTERED = 'registered';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'permits')]
    private ?User $employee = null;

    #[Assert\GreaterThanOrEqual('now', message: 'Inserire data/ora futura')]
    #[ORM\Column]
    private ?\DateTimeImmutable $startAt = null;

//    #[Assert\Expression('endAt > startAt', message: 'Inserire data/ora successiva all\'inizio')]
    #[ORM\Column]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column]
    private ?int $days = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1)]
    private ?string $hours = '0';

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $evaluatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $requestAt = null;

    #[ORM\Column(length: 50)]
    private ?string $permitType = null;

    #[ORM\Column]
    #[Assert\Expression(
        "(this.getPermitType() == 'Permesso' and this.isAgreeUnpaid() == true) or
        this.getPermitType() != 'Permesso' and (this.isAgreeUnpaid() == false)",
        message: 'Solo se hai chiesto permesso è obbligatorio esprimere il consenso alla decurtazione.',
    )]
    private ?bool $agreeUnpaid = false;

    public function __construct()
    {
        $this->requestAt = new \DateTimeImmutable();
//        $this->status = 'start'; //before marking workflow
    }

    public function __toString(): string
    {
        return sprintf('Start %s for %d days and %d hours', $this->startAt->format('d/m/y H:i'), $this->days, $this->hours);
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

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function setDays(int $days): static
    {
        $this->days = $days;

        return $this;
    }

    public function getHours(): ?string
    {
        return $this->hours;
    }

    public function setHours(?string $hours): static
    {
        $this->hours = $hours;

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

    public function getEvaluatedAt(): ?\DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function setEvaluatedAt(?\DateTimeImmutable $evaluatedAt): static
    {
        $this->evaluatedAt = $evaluatedAt;

        return $this;
    }

    public function getRequestAt(): ?\DateTimeImmutable
    {
        return $this->requestAt;
    }

    public function setRequestAt(\DateTimeImmutable $requestAt): static
    {
        $this->requestAt = $requestAt;

        return $this;
    }

    public function getPermitType(): ?string
    {
        return $this->permitType;
    }

    public function setPermitType(string $permitType): static
    {
        $this->permitType = $permitType;

        return $this;
    }

    public function isAgreeUnpaid(): ?bool
    {
        return $this->agreeUnpaid;
    }

    public function setAgreeUnpaid(bool $agreeUnpaid): static
    {
        $this->agreeUnpaid = $agreeUnpaid;

        return $this;
    }
}
