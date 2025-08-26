<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PixType: string
{
    case NORMAL = 'NORMAL';
    case RECORRENTE = 'RECORRENTE';

    public static function fromString(string $value): self
    {
        $upperValue = strtoupper($value);
        
        if ($upperValue === 'NORMAL') {
            return self::NORMAL;
        }
        
        if ($upperValue === 'RECORRENTE') {
            return self::RECORRENTE;
        }
        
        throw new \InvalidArgumentException("Tipo PIX inválido: {$value}");
    }
}
