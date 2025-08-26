<?php

declare(strict_types=1);

namespace App\Services\Impl;

use App\Config\Env;
use App\Services\Contracts\MessageBusInterface;

class RabbitMqBus implements MessageBusInterface
{
    public function publish(string $routingKey, array $payload): void
    {
        try {
            // Mock simples para RabbitMQ - em produção usaria php-amqplib
            $message = [
                'routing_key' => $routingKey,
                'payload' => $payload,
                'timestamp' => date('c'),
                'exchange' => 'domain.events'
            ];
            
            // Log da mensagem (em produção seria enviada para RabbitMQ)
            error_log("Evento publicado: {$routingKey} -> " . json_encode($payload));
            
            // Simula delay de processamento
            usleep(100000); // 100ms
            
        } catch (\Exception $e) {
            error_log("Erro ao publicar evento: " . $e->getMessage());
        }
    }
}
