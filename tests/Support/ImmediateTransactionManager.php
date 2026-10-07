<?php

namespace Tests\Support;

use App\Application\Shared\TransactionManager;

final class ImmediateTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return $callback();
    }
}
