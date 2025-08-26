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

try {
    echo "Iniciando geração de relatório diário...\n";
    
    $reportService = Container::getReportService();
    $reportService->generateDailyReport();
    
    echo "Relatório diário gerado com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro ao gerar relatório: " . $e->getMessage() . "\n";
    exit(1);
}
