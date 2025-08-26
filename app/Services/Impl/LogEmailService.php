<?php

declare(strict_types=1);

namespace App\Services\Impl;

use App\Services\Contracts\EmailServiceInterface;

class LogEmailService implements EmailServiceInterface
{
    private $storagePath;

    public function __construct()
    {
        $this->storagePath = __DIR__ . '/../../storage/mails';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function sendConfirmation(array $pixData): void
    {
        $subject = 'PIX Aprovado - Confirmação';
        $body = $this->generateConfirmationBody($pixData);
        
        $filename = sprintf(
            'confirmation_%s_%s.eml',
            $pixData['external_id'],
            date('Y-m-d_H-i-s')
        );
        
        $this->saveEmail($filename, $subject, $body);
    }

    public function sendWithAttachment(string $to, string $subject, string $body, string $attachmentPath): void
    {
        $filename = sprintf(
            'report_%s.eml',
            date('Y-m-d_H-i-s')
        );
        
        $fullBody = $body . "\n\nAnexo: " . basename($attachmentPath);
        $this->saveEmail($filename, $subject, $fullBody);
    }

    private function generateConfirmationBody(array $pixData): string
    {
        return sprintf(
            "Olá!\n\n" .
            "Seu PIX foi aprovado com sucesso!\n\n" .
            "Detalhes:\n" .
            "- ID: %s\n" .
            "- Valor: R$ %.2f\n" .
            "- Tipo: %s\n" .
            "- Data de Aprovação: %s\n\n" .
            "Obrigado por escolher nossos serviços!\n\n" .
            "Atenciosamente,\n" .
            "Equipe PIX",
            $pixData['external_id'],
            $pixData['amount'],
            $pixData['type'],
            $pixData['approved_at']
        );
    }

    private function saveEmail(string $filename, string $subject, string $body): void
    {
        $filepath = $this->storagePath . '/' . $filename;
        
        $emailContent = sprintf(
            "From: noreply@pix.com\r\n" .
            "To: %s\r\n" .
            "Subject: %s\r\n" .
            "Date: %s\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 8bit\r\n\r\n" .
            "%s",
            'user@example.com',
            $subject,
            date('r'),
            $body
        );
        
        file_put_contents($filepath, $emailContent);
        
        error_log("Email salvo: {$filepath}");
    }
}
