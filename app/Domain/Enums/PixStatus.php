<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PixStatus: string
{
    case PENDENTE = 'PENDENTE';
    case APROVADO = 'APROVADO';
    case EXPIRADO = 'EXPIRADO';

    public static function fromString(string $value): self
    {
        $upperValue = strtoupper($value);
        
        if ($upperValue === 'PENDENTE') {
            return self::PENDENTE;
        }
        
        if ($upperValue === 'APROVADO') {
            return self::APROVADO;
        }
        
        if ($upperValue === 'EXPIRADO') {
            return self::EXPIRADO;
        }
        
        throw new \InvalidArgumentException("Status PIX inválido: {$value}");
    }
}
