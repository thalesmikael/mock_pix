<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface RecoveryServiceInterface
{
    public function notifyExpiredPix(array $pixData): void;
}
