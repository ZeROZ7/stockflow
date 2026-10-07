<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function manager(): User
{
    return User::factory()->manager()->create();
}

function staff(): User
{
    return User::factory()->create();
}
