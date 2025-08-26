<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Config\Container;
use App\Services\Contracts\PixServiceInterface;

class WebhookController
{
    private $pixService;

    public function __construct()
    {
        $this->pixService = Container::getPixService();
    }

    public function pixExpired(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || empty($input['external_id'])) {
                return $this->errorResponse('external_id é obrigatório', 422);
            }

            $result = $this->pixService->expireByExternalId($input['external_id']);
            
            // Webhook é idempotente, sempre retorna 200
            http_response_code(200);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'PIX expirado com sucesso',
                    'pix' => $result['pix']
                ];
            }
            
            return [
                'success' => true,
                'message' => 'PIX não encontrado ou já não está pendente',
                'pix' => null
            ];
            
        } catch (\Exception $e) {
            error_log("Erro no webhook de expiração: " . $e->getMessage());
            // Webhook sempre retorna 200 mesmo com erro
            http_response_code(200);
            return [
                'success' => false,
                'error' => 'Erro interno',
                'message' => 'PIX processado (idempotente)'
            ];
        }
    }

    private function errorResponse(string $message, int $statusCode): array
    {
        http_response_code($statusCode);
        return [
            'success' => false,
            'error' => $message,
            'status_code' => $statusCode
        ];
    }
}
