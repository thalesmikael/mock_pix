<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis de ambiente
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            $_SERVER[trim($key)] = trim($value);
        }
    }
}

use App\Config\Container;
use App\Services\Contracts\EmailServiceInterface;
use App\Services\Contracts\RecoveryServiceInterface;

class Worker
{
    private $emailService;
    private $recoveryService;

    public function __construct()
    {
        $this->emailService = Container::getEmailService();
        $this->recoveryService = Container::getRecoveryService();
        
        echo "Worker inicializado\n";
    }

    public function start(): void
    {
        echo "Iniciando worker...\n";
        echo "Este é um worker mock que simula o processamento de eventos\n";
        echo "Em produção, consumiria as filas RabbitMQ\n\n";
        
        // Simula processamento de eventos
        $this->simulateEventProcessing();
    }

    private function simulateEventProcessing(): void
    {
        echo "Simulando processamento de eventos...\n";
        
        // Simula evento de aprovação
        $this->simulateEmailConfirmation();
        
        // Simula evento de expiração
        $this->simulateSalesRecovery();
        
        echo "Simulação concluída. Worker finalizado.\n";
    }

    private function simulateEmailConfirmation(): void
    {
        echo "Simulando confirmação de email...\n";
        
        $pixData = [
            'pix_id' => 1,
            'external_id' => 'abc-123',
            'payer_email' => 'user@example.com',
            'amount' => 50.00,
            'type' => 'NORMAL',
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        $approvedAt = new DateTimeImmutable($pixData['approved_at']);
        $now = new DateTimeImmutable();
        
        // Calcula SLA
        $slaSeconds = $now->getTimestamp() - $approvedAt->getTimestamp();
        $slaMinutes = $slaSeconds / 60;
        
        echo "Processando confirmação de email para PIX {$pixData['external_id']}\n";
        echo "SLA: {$slaMinutes} minutos\n";
        
        if ($slaMinutes > 2) {
            echo "ALERTA: SLA excedido! ({$slaMinutes} minutos > 2 minutos)\n";
        }
        
        $this->emailService->sendConfirmation($pixData);
        echo "Email de confirmação processado com sucesso\n\n";
    }

    private function simulateSalesRecovery(): void
    {
        echo "Simulando recuperação de vendas...\n";
        
        $pixData = [
            'pix_id' => 1,
            'external_id' => 'abc-123',
            'payer_email' => 'user@example.com',
            'expired_at' => date('Y-m-d H:i:s')
        ];
        
        echo "Processando recuperação de vendas para PIX {$pixData['external_id']}\n";
        
        $this->recoveryService->notifyExpiredPix($pixData);
        
        echo "Recuperação de vendas processada com sucesso\n\n";
    }
}

// Inicia o worker
try {
    $worker = new Worker();
    $worker->start();
} catch (Exception $e) {
    echo "Erro fatal no worker: " . $e->getMessage() . "\n";
    exit(1);
}
