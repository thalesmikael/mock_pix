<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
