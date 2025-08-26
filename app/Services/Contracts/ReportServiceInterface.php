<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface ReportServiceInterface
{
    public function generateDailyReport(): void;
}
