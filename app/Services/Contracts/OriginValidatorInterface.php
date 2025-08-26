<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface OriginValidatorInterface
{
    public function isValid(string $origin): bool;
}
