<?php

use App\Models\User;

it('issues the XSRF cookie the SPA needs before logging in', function () {
    $this->get('/sanctum/csrf-cookie')
        ->assertNoContent()
        ->assertCookie('XSRF-TOKEN');
});

it('logs in with a session cookie and never returns a token', function () {
    $user = User::factory()->create(['email' => 'demo@stockflow.test']);

    $response = $this->postJson('/auth/login', [
        'email' => 'demo@stockflow.test',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.email', 'demo@stockflow.test')
        ->assertJsonMissingPath('data.token');

    $this->assertAuthenticatedAs($user);
    $this->getJson('/api/user')->assertOk()->assertJsonPath('data.id', $user->id);
});

it('rejects wrong credentials', function () {
    User::factory()->create(['email' => 'demo@stockflow.test']);

    $this->postJson('/auth/login', ['email' => 'demo@stockflow.test', 'password' => 'nope'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertGuest();
});

it('logs out and invalidates the session', function () {
    $this->actingAs(staff())
        ->postJson('/auth/logout')
        ->assertNoContent();

    $this->assertGuest('web');
});

it('returns 401 from the API to guests', function (string $uri) {
    $this->getJson($uri)->assertUnauthorized();
})->with(['/api/user', '/api/dashboard', '/api/products', '/api/purchase-orders']);

it('serves the Vue SPA for front-end routes', function (string $uri) {
    $this->withoutVite()
        ->get($uri)
        ->assertOk()
        ->assertSee('id="app"', escape: false);
})->with(['/', '/login', '/purchase-orders/1']);
