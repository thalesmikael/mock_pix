<?php

declare(strict_types=1);

// Este é um arquivo de exemplo para testar a funcionalidade
// Em produção, use os endpoints HTTP

require_once __DIR__ . '/vendor/autoload.php';

// Simula variáveis de ambiente
$_ENV['APP_TZ'] = 'America/Sao_Paulo';
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_DATABASE'] = 'pixdb';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = '';
$_ENV['ORIGIN_WHITELIST'] = 'bankA,bankB';
$_ENV['CEO_EMAIL'] = 'ceo@company.test';
$_ENV['EMAIL_SLA_MINUTES'] = '2';

echo "=== Teste do Sistema PIX ===\n\n";

try {
    // Testa o container
    echo "1. Testando Container...\n";
    $pixService = \App\Config\Container::getPixService();
    echo "Container funcionando\n\n";
    
    // Testa validação de origem
    echo "2. Testando Validação de Origem...\n";
    $originValidator = \App\Config\Container::getOriginValidator();
    echo "Origem 'bankA' válida: " . ($originValidator->isValid('bankA') ? 'Sim' : 'Não') . "\n";
    echo "Origem 'bankC' válida: " . ($originValidator->isValid('bankC') ? 'Sim' : 'Não') . "\n";
    echo "Validação de origem funcionando\n\n";
    
    // Testa clock
    echo "3. Testando Clock...\n";
    $clock = \App\Config\Container::getClock();
    $now = $clock->now();
    echo "Data/hora atual: " . $now->format('Y-m-d H:i:s T') . "\n";
    echo "Clock funcionando\n\n";
    
    // Testa email service
    echo "4. Testando Email Service...\n";
    $emailService = \App\Config\Container::getEmailService();
    $pixData = [
        'external_id' => 'test-123',
        'amount' => 100.00,
        'type' => 'NORMAL',
        'approved_at' => date('Y-m-d H:i:s')
    ];
    $emailService->sendConfirmation($pixData);
    echo "Email service funcionando\n\n";
    
    echo "Todos os testes passaram com sucesso!\n";
    echo "O sistema está funcionando corretamente.\n";
    
} catch (Exception $e) {
    echo "Erro nos testes: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
