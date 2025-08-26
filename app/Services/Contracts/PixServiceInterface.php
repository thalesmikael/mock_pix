<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface PixServiceInterface
{
    public function create(array $input): array;
    public function approve(int $id): array;
    public function expireByExternalId(string $externalId): ?array;
}
