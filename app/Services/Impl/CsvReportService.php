<?php

declare(strict_types=1);

namespace App\Services\Impl;

use App\Config\Env;
use App\Models\PixModel;
use App\Services\Contracts\ClockInterface;
use App\Services\Contracts\EmailServiceInterface;
use App\Services\Contracts\ReportServiceInterface;
use DateTimeImmutable;
use DateTimeZone;

class CsvReportService implements ReportServiceInterface
{
    private $storagePath;
    private $emailService;
    private $clock;
    private $pixModel;

    public function __construct(
        EmailServiceInterface $emailService,
        ClockInterface $clock,
        PixModel $pixModel = null
    ) {
        $this->emailService = $emailService;
        $this->clock = $clock;
        $this->pixModel = $pixModel ?: new PixModel();
        
        $this->storagePath = __DIR__ . '/../../storage/reports';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function generateDailyReport(): void
    {
        // Calcula ontem no timezone do app
        $timezone = new DateTimeZone(Env::get('APP_TZ', 'America/Sao_Paulo'));
        $yesterday = $this->clock->now()->setTimezone($timezone)->modify('-1 day');
        
        $totals = $this->pixModel->totalsForDate($yesterday);
        $csvPath = $this->generateCsv($totals, $yesterday);
        
        // Envia relatório por email
        $ceoEmail = Env::get('CEO_EMAIL', 'ceo@company.test');
        $subject = sprintf('Relatório Diário PIX - %s', $totals['date']);
        $body = $this->generateReportBody($totals);
        
        $this->emailService->sendWithAttachment($ceoEmail, $subject, $body, $csvPath);
        
        error_log("Relatório diário gerado para {$totals['date']}: {$csvPath}");
    }

    private function generateCsv(array $totals, DateTimeImmutable $date): string
    {
        $filename = sprintf('report_%s.csv', $date->format('Y-m-d'));
        $filepath = $this->storagePath . '/' . $filename;
        
        $csvContent = "Data,Status,Quantidade,Valor Total\n";
        $csvContent .= sprintf(
            "%s,APROVADO,%d,R$ %.2f\n",
            $totals['date'],
            $totals['approved']['count'],
            $totals['approved']['total']
        );
        $csvContent .= sprintf(
            "%s,EXPIRADO,%d,\n",
            $totals['date'],
            $totals['expired']['count']
        );
        
        file_put_contents($filepath, $csvContent);
        
        return $filepath;
    }

    private function generateReportBody(array $totals): string
    {
        return sprintf(
            "Relatório Diário PIX - %s\n\n" .
            "Resumo:\n" .
            "- PIXs Aprovados: %d (Total: R$ %.2f)\n" .
            "- PIXs Expirados: %d\n\n" .
            "Anexo: Relatório detalhado em CSV",
            $totals['date'],
            $totals['approved']['count'],
            $totals['approved']['total'],
            $totals['expired']['count']
        );
    }
}
