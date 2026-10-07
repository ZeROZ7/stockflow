<?php

namespace App\Application\Shared;

interface TransactionManager
{
    /**
     * Run the callback atomically and return its result.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function run(callable $callback): mixed;
}
