<?php

declare(strict_types=1);

namespace App\Services\Impl;

use App\Services\Contracts\RecoveryServiceInterface;

class HttpRecoveryService implements RecoveryServiceInterface
{
    public function notifyExpiredPix(array $pixData): void
    {
        $message = sprintf(
            'Recovery notified for expired PIX: ID=%s, External=%s, Email=%s, Expired=%s',
            $pixData['pix_id'],
            $pixData['external_id'],
            $pixData['payer_email'],
            $pixData['expired_at']
        );
        
        error_log($message);
    }
}
