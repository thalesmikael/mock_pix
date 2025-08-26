<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface EmailServiceInterface
{
    public function sendConfirmation(array $pixData): void;
    public function sendWithAttachment(string $to, string $subject, string $body, string $attachmentPath): void;
}
