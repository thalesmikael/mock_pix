<?php

declare(strict_types=1);

namespace App\Services\Impl;

use App\Config\Env;
use App\Services\Contracts\ClockInterface;
use DateTimeImmutable;
use DateTimeZone;

class SystemClock implements ClockInterface
{
    private $timezone;

    public function __construct()
    {
        $timezoneName = Env::get('APP_TZ', 'America/Sao_Paulo');
        $this->timezone = new DateTimeZone($timezoneName);
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}
