<?php

declare(strict_types=1);

namespace App\Config;

use App\Http\Controllers\HealthController;
use App\Http\Controllers\PixController;
use App\Http\Controllers\WebhookController;

class Router
{
    private $routes = [];

    public function __construct()
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // Health check
        $this->routes['GET']['/health'] = [HealthController::class, 'check'];
        
        // PIX endpoints
        $this->routes['POST']['/pix'] = [PixController::class, 'create'];
        $this->routes['POST']['/pix/{id}/approve'] = [PixController::class, 'approve'];
        
        // Webhook endpoints
        $this->routes['POST']['/webhooks/pix-expired'] = [WebhookController::class, 'pixExpired'];
    }

    public function dispatch(string $method, string $uri): array
    {
        $method = strtoupper($method);
        
        // Remove query string da URI
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Procura rota exata primeiro
        if (isset($this->routes[$method][$uri])) {
            return $this->executeRoute($this->routes[$method][$uri], []);
        }
        
        // Procura rotas com parâmetros
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $params = $this->matchRoute($route, $uri);
            if ($params !== null) {
                return $this->executeRoute($handler, $params);
            }
        }
        
        // Rota não encontrada
        http_response_code(404);
        return [
            'success' => false,
            'error' => 'Rota não encontrada',
            'method' => $method,
            'uri' => $uri
        ];
    }

    private function matchRoute(string $route, string $uri): ?array
    {
        $routeParts = explode('/', trim($route, '/'));
        $uriParts = explode('/', trim($uri, '/'));
        
        if (count($routeParts) !== count($uriParts)) {
            return null;
        }
        
        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            if (strpos($routeParts[$i], '{') === 0 && substr($routeParts[$i], -1) === '}') {
                $paramName = trim($routeParts[$i], '{}');
                $params[$paramName] = $uriParts[$i];
            } elseif ($routeParts[$i] !== $uriParts[$i]) {
                return null;
            }
        }
        
        return $params;
    }

    private function executeRoute(array $handler, array $params): array
    {
        list($controllerClass, $method) = $handler;
        $controller = new $controllerClass();
        
        if (empty($params)) {
            return $controller->$method();
        }
        
        // Para rotas com parâmetros, assume que o primeiro é o ID
        if (isset($params['id'])) {
            return $controller->$method((int) $params['id']);
        }
        
        return $controller->$method();
    }
}
