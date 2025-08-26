<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface MessageBusInterface
{
    public function publish(string $routingKey, array $payload): void;
}
