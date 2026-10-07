<?php

// Guard rails for the hexagonal structure of the purchasing module.

arch('the domain is plain PHP, independent of Laravel')
    ->expect('App\Domain')
    ->not->toUse(['Illuminate', 'App\Models', 'App\Http', 'App\Infrastructure', 'App\Application']);

arch('use cases depend on ports, not on Eloquent or HTTP')
    ->expect('App\Application')
    ->not->toUse(['Illuminate', 'App\Models', 'App\Http', 'App\Infrastructure']);

arch('controllers do not talk to infrastructure adapters directly')
    ->expect('App\Http')
    ->not->toUse('App\Infrastructure');

arch('no debugging leftovers')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();
