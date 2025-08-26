<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Enums\PixStatus;
use App\Domain\Enums\PixType;
use DateTimeImmutable;
use InvalidArgumentException;

class Pix
{
    private $id;
    private $externalId;
    private $payerEmail;
    private $amount;
    private $type;
    private $status;
    private $origin;
    private $createdAt;
    private $approvedAt;
    private $expiredAt;

    public function __construct(
        ?int $id,
        string $externalId,
        string $payerEmail,
        float $amount,
        PixType $type,
        PixStatus $status,
        string $origin,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $approvedAt = null,
        ?DateTimeImmutable $expiredAt = null
    ) {
        $this->id = $id;
        $this->externalId = $externalId;
        $this->payerEmail = $payerEmail;
        $this->amount = $amount;
        $this->type = $type;
        $this->status = $status;
        $this->origin = $origin;
        $this->createdAt = $createdAt;
        $this->approvedAt = $approvedAt;
        $this->expiredAt = $expiredAt;

        if ($amount <= 0) {
            throw new InvalidArgumentException('Valor deve ser maior que zero');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getPayerEmail(): string
    {
        return $this->payerEmail;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getType(): PixType
    {
        return $this->type;
    }

    public function getStatus(): PixStatus
    {
        return $this->status;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getApprovedAt(): ?DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function getExpiredAt(): ?DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function approve(DateTimeImmutable $now): void
    {
        if ($this->status !== PixStatus::PENDENTE) {
            throw new InvalidArgumentException('PIX deve estar pendente para ser aprovado');
        }

        $this->status = PixStatus::APROVADO;
        $this->approvedAt = $now;
    }

    public function expire(DateTimeImmutable $now): void
    {
        if ($this->status !== PixStatus::PENDENTE) {
            throw new InvalidArgumentException('PIX deve estar pendente para expirar');
        }

        $this->status = PixStatus::EXPIRADO;
        $this->expiredAt = $now;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->externalId,
            'payer_email' => $this->payerEmail,
            'amount' => $this->amount,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'origin' => $this->origin,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'approved_at' => $this->approvedAt ? $this->approvedAt->format('Y-m-d H:i:s') : null,
            'expired_at' => $this->expiredAt ? $this->expiredAt->format('Y-m-d H:i:s') : null,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['external_id'],
            $data['payer_email'],
            (float) $data['amount'],
            PixType::fromString($data['type']),
            PixStatus::fromString($data['status']),
            $data['origin'],
            new DateTimeImmutable($data['created_at']),
            isset($data['approved_at']) ? new DateTimeImmutable($data['approved_at']) : null,
            isset($data['expired_at']) ? new DateTimeImmutable($data['expired_at']) : null
        );
    }
}
