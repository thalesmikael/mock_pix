<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class HealthController
{
    public function check(): array
    {
        http_response_code(200);
        return [
            'status' => 'OK',
            'timestamp' => date('c'),
            'service' => 'Mock PIX API',
            'version' => '1.0.0'
        ];
    }
}
