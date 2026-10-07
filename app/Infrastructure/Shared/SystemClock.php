<?php

namespace App\Infrastructure\Shared;

use App\Application\Shared\Clock;
use DateTimeImmutable;

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return now()->toDateTimeImmutable();
    }
}
