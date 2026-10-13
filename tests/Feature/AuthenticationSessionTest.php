<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['session.driver' => 'database']);
    config(['sanctum.stateful' => ['localhost:5173', '127.0.0.1:5173']]);
});

function authAbsorbCookies($response, array &$jar): void
{
    foreach ($response->baseResponse->headers->getCookies() as $cookie) {
        $jar[$cookie->getName()] = $cookie->getValue();
    }
}

function authPrimeCsrf($test, array &$jar): void
{
    $test->get('/sanctum/csrf-cookie')->assertNoContent();
}

function authForgetGuards($test): void
{
    app('auth')->forgetGuards();
}

function authStatefulRequest($test, string $method, string $uri, array $data, array &$jar)
{
    authForgetGuards($test);

    $server = [
        'HTTP_REFERER' => 'http://localhost:5173/',
        'HTTP_ACCEPT' => 'application/json',
    ];

    if (isset($jar['XSRF-TOKEN'])) {
        $server['HTTP_X_XSRF_TOKEN'] = $jar['XSRF-TOKEN'];
    }

    $content = '';

    if (! in_array(strtoupper($method), ['GET', 'HEAD'])) {
        $server['CONTENT_TYPE'] = 'application/json';
        $content = json_encode($data);
    }

    $response = $test->call($method, $uri, [], $jar, [], $server, $content);

    authAbsorbCookies($response, $jar);

    return $response;
}

it('registers a user via the SPA flow, creates an authenticated session, and returns no token', function () {
    $jar = [];

    authPrimeCsrf($this, $jar);

    $response = authStatefulRequest($this, 'POST', '/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $jar);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonMissingPath('data.token')
        ->assertJsonPath('data.user.email', 'jane@example.com');

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
    ]);

    authStatefulRequest($this, 'GET', '/api/user', [], $jar)
        ->assertStatus(200)
        ->assertJsonPath('email', 'jane@example.com');
});

it('rejects a stateful registration that fails validation', function () {
    $jar = [];

    authPrimeCsrf($this, $jar);

    authStatefulRequest($this, 'POST', '/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'does-not-match',
    ], $jar)->assertStatus(422);
});

it('logs a user in through the session, serves the session user, and logs out', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $jar = [];

    authPrimeCsrf($this, $jar);

    $login = authStatefulRequest($this, 'POST', '/api/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ], $jar);

    $login->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonMissingPath('data.token')
        ->assertJsonPath('data.user.email', 'john@example.com');

    $this->assertDatabaseCount('personal_access_tokens', 0);

    authStatefulRequest($this, 'GET', '/api/user', [], $jar)
        ->assertStatus(200)
        ->assertJsonPath('email', 'john@example.com');

    $logout = authStatefulRequest($this, 'POST', '/api/logout', [], $jar);

    $logout->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Logged out successfully.');

    authStatefulRequest($this, 'GET', '/api/user', [], $jar)
        ->assertStatus(401);
});

it('returns 422 with the preserved error contract for invalid credentials', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $jar = [];

    authPrimeCsrf($this, $jar);

    authStatefulRequest($this, 'POST', '/api/login', [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ], $jar)
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'Invalid credentials.');
});

it('rejects login for a soft-deleted user', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $user->delete();

    $jar = [];

    authPrimeCsrf($this, $jar);

    authStatefulRequest($this, 'POST', '/api/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ], $jar)
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', 'Invalid credentials.');
});

it('still allows an inactive user to log in (existing status behavior preserved)', function () {
    User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => 'password123',
        'status' => 'inactive',
    ]);

    $jar = [];

    authPrimeCsrf($this, $jar);

    authStatefulRequest($this, 'POST', '/api/login', [
        'email' => 'inactive@example.com',
        'password' => 'password123',
    ], $jar)
        ->assertStatus(200)
        ->assertJsonPath('data.user.status', 'inactive');
});

it('requires a valid CSRF token for stateful API writes', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $jar = [];

    authPrimeCsrf($this, $jar);

    $server = [
        'HTTP_REFERER' => 'http://localhost:5173/',
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
    ];

    $this->app->instance('env', 'production');

    try {
        $this->call('POST', '/api/login', [], $jar, [], $server, '{"email":"john@example.com","password":"password123"}')
            ->assertStatus(419);
    } finally {
        $this->app->instance('env', 'testing');
    }
});
