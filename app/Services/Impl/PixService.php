<?php

declare(strict_types=1);

namespace App\Services\Impl;

use App\Domain\Enums\PixStatus;
use App\Domain\Enums\PixType;
use App\Models\Pix;
use App\Models\PixModel;
use App\Services\Contracts\ClockInterface;
use App\Services\Contracts\MessageBusInterface;
use App\Services\Contracts\OriginValidatorInterface;
use App\Services\Contracts\PixServiceInterface;
use InvalidArgumentException;

class PixService implements PixServiceInterface
{
    private $messageBus;
    private $originValidator;
    private $clock;
    private $pixModel;

    public function __construct(
        MessageBusInterface $messageBus,
        OriginValidatorInterface $originValidator,
        ClockInterface $clock,
        PixModel $pixModel = null
    ) {
        $this->messageBus = $messageBus;
        $this->originValidator = $originValidator;
        $this->clock = $clock;
        $this->pixModel = $pixModel ?: new PixModel();
    }

    public function create(array $input): array
    {
        $this->validateCreateInput($input);

        $now = $this->clock->now();
        
        $pixData = [
            'external_id' => $input['external_id'],
            'payer_email' => $input['payer_email'],
            'amount' => (float) $input['amount'],
            'type' => $input['type'],
            'status' => PixStatus::PENDENTE->value,
            'origin' => $input['origin'],
            'created_at' => $now->format('Y-m-d H:i:s'),
        ];

        $pix = $this->pixModel->insert($pixData);

        return [
            'success' => true,
            'pix' => $pix->toArray(),
            'message' => 'PIX criado com sucesso'
        ];
    }

    public function approve(int $id): array
    {
        $pix = $this->pixModel->findById($id);
        if (!$pix) {
            throw new InvalidArgumentException('PIX não encontrado');
        }

        $now = $this->clock->now();
        $pix->approve($now);
        $this->pixModel->update($pix);

        // Publicar evento de aprovação
        $this->messageBus->publish('pix.approved', [
            'pix_id' => $pix->getId(),
            'external_id' => $pix->getExternalId(),
            'payer_email' => $pix->getPayerEmail(),
            'approved_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'pix' => $pix->toArray(),
            'message' => 'PIX aprovado com sucesso'
        ];
    }

    public function expireByExternalId(string $externalId): ?array
    {
        $pix = $this->pixModel->findByExternalId($externalId);
        if (!$pix) {
            return null; // Idempotente: não existe = não faz nada
        }

        if ($pix->getStatus() !== PixStatus::PENDENTE) {
            return null; // Idempotente: já não está pendente
        }

        $now = $this->clock->now();
        $pix->expire($now);
        $this->pixModel->update($pix);

        // Publicar evento de expiração
        $this->messageBus->publish('pix.expired', [
            'pix_id' => $pix->getId(),
            'external_id' => $pix->getExternalId(),
            'payer_email' => $pix->getPayerEmail(),
            'expired_at' => $now->format('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'pix' => $pix->toArray(),
            'message' => 'PIX expirado com sucesso'
        ];
    }

    private function validateCreateInput(array $input): void
    {
        $required = ['external_id', 'payer_email', 'amount', 'type', 'origin'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new InvalidArgumentException("Campo obrigatório: {$field}");
            }
        }

        if (!filter_var($input['payer_email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }

        if ((float) $input['amount'] <= 0) {
            throw new InvalidArgumentException('Valor deve ser maior que zero');
        }

        try {
            PixType::fromString($input['type']);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Tipo PIX inválido');
        }

        if (!$this->originValidator->isValid($input['origin'])) {
            throw new InvalidArgumentException('Origem não autorizada');
        }
    }
}
