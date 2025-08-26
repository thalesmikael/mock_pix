<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Config\Container;
use App\Services\Contracts\PixServiceInterface;
use InvalidArgumentException;

class PixController
{
    private $pixService;

    public function __construct()
    {
        $this->pixService = Container::getPixService();
    }

    public function create(): array
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                return $this->errorResponse('Dados inválidos', 422);
            }

            $result = $this->pixService->create($input);
            
            http_response_code(201);
            return $result;
            
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            error_log("Erro ao criar PIX: " . $e->getMessage());
            return $this->errorResponse('Erro interno do servidor', 500);
        }
    }

    public function approve(int $id): array
    {
        try {
            $result = $this->pixService->approve($id);
            
            http_response_code(200);
            return $result;
            
        } catch (InvalidArgumentException $e) {
            if (strpos($e->getMessage(), 'não encontrado') !== false) {
                return $this->errorResponse($e->getMessage(), 404);
            }
            return $this->errorResponse($e->getMessage(), 409);
        } catch (\Exception $e) {
            error_log("Erro ao aprovar PIX: " . $e->getMessage());
            return $this->errorResponse('Erro interno do servidor', 500);
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
