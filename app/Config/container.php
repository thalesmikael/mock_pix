<?php

declare(strict_types=1);

namespace App\Config;

use App\Services\Contracts\ClockInterface;
use App\Services\Contracts\EmailServiceInterface;
use App\Services\Contracts\MessageBusInterface;
use App\Services\Contracts\OriginValidatorInterface;
use App\Services\Contracts\PixServiceInterface;
use App\Services\Contracts\RecoveryServiceInterface;
use App\Services\Contracts\ReportServiceInterface;
use App\Services\Impl\CsvReportService;
use App\Services\Impl\HttpRecoveryService;
use App\Services\Impl\LogEmailService;
use App\Services\Impl\PixService;
use App\Services\Impl\RabbitMqBus;
use App\Services\Impl\SystemClock;
use App\Services\Impl\WhitelistOriginValidator;

class Container
{
    private static $instances = [];

    public static function getPixService(): PixServiceInterface
    {
        if (!isset(self::$instances[PixServiceInterface::class])) {
            self::$instances[PixServiceInterface::class] = new PixService(
                self::getMessageBus(),
                self::getOriginValidator(),
                self::getClock()
            );
        }

        return self::$instances[PixServiceInterface::class];
    }

    public static function getEmailService(): EmailServiceInterface
    {
        if (!isset(self::$instances[EmailServiceInterface::class])) {
            self::$instances[EmailServiceInterface::class] = new LogEmailService();
        }

        return self::$instances[EmailServiceInterface::class];
    }

    public static function getRecoveryService(): RecoveryServiceInterface
    {
        if (!isset(self::$instances[RecoveryServiceInterface::class])) {
            self::$instances[RecoveryServiceInterface::class] = new HttpRecoveryService();
        }

        return self::$instances[RecoveryServiceInterface::class];
    }

    public static function getReportService(): ReportServiceInterface
    {
        if (!isset(self::$instances[ReportServiceInterface::class])) {
            self::$instances[ReportServiceInterface::class] = new CsvReportService(
                self::getEmailService(),
                self::getClock()
            );
        }

        return self::$instances[ReportServiceInterface::class];
    }

    public static function getMessageBus(): MessageBusInterface
    {
        if (!isset(self::$instances[MessageBusInterface::class])) {
            self::$instances[MessageBusInterface::class] = new RabbitMqBus();
        }

        return self::$instances[MessageBusInterface::class];
    }

    public static function getOriginValidator(): OriginValidatorInterface
    {
        if (!isset(self::$instances[OriginValidatorInterface::class])) {
            self::$instances[OriginValidatorInterface::class] = new WhitelistOriginValidator();
        }

        return self::$instances[OriginValidatorInterface::class];
    }

    public static function getClock(): ClockInterface
    {
        if (!isset(self::$instances[ClockInterface::class])) {
            self::$instances[ClockInterface::class] = new SystemClock();
        }

        return self::$instances[ClockInterface::class];
    }
}
