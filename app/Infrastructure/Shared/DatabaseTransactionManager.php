<?php

namespace App\Infrastructure\Shared;

use App\Application\Shared\TransactionManager;
use Illuminate\Database\ConnectionInterface;

final readonly class DatabaseTransactionManager implements TransactionManager
{
    public function __construct(private ConnectionInterface $db) {}

    public function run(callable $callback): mixed
    {
        return $this->db->transaction(fn () => $callback());
    }
}
