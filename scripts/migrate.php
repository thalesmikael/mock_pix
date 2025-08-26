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

try {
    echo "Iniciando migração do banco de dados...\n";
    
    // Conecta no MySQL (sem especificar database para poder criar)
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $user = $_ENV['DB_USERNAME'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "Conectado ao MySQL em {$host}:{$port}\n";
    
    // Lê e executa o script SQL
    $sqlFile = __DIR__ . '/migrate.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo migrate.sql não encontrado");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "Executando script SQL...\n";
    
    // Divide o SQL em comandos individuais
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($commands as $command) {
        if (empty($command)) continue;
        
        echo "Executando: " . substr($command, 0, 50) . "...\n";
        $pdo->exec($command);
    }
    
    echo "Migração concluída com sucesso!\n";
    echo "Banco 'pixdb' criado e tabela 'pix' configurada.\n";
    
} catch (Exception $e) {
    echo "Erro na migração: " . $e->getMessage() . "\n";
    exit(1);
}
