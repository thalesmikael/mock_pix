<?php

declare(strict_types=1);

namespace App\Services\Impl;

use App\Config\Env;
use App\Services\Contracts\OriginValidatorInterface;

class WhitelistOriginValidator implements OriginValidatorInterface
{
    private $whitelist;

    public function __construct()
    {
        $this->whitelist = Env::getArray('ORIGIN_WHITELIST');
    }

    public function isValid(string $origin): bool
    {
        return in_array($origin, $this->whitelist, true);
    }
}
