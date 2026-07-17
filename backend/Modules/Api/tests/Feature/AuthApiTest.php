<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('registers a shopper and returns a token', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Ana Pop',
        'email' => 'ana@example.com',
        'password' => 'parola-secreta',
        'password_confirmation' => 'parola-secreta',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
        ->assertJsonPath('user.email', 'ana@example.com');

    expect($response->json('token'))->toBeString()->not->toBeEmpty();

    $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
    $this->assertDatabaseHas('personal_access_tokens', ['name' => 'mobile']);
});

it('rejects registration with a duplicate email as 422', function () {
    User::factory()->create(['email' => 'ana@example.com']);

    $this->postJson('/api/v1/register', [
        'name' => 'Ana Pop',
        'email' => 'ana@example.com',
        'password' => 'parola-secreta',
        'password_confirmation' => 'parola-secreta',
    ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['email']]);
});

it('logs a shopper in and returns a token', function () {
    User::factory()->create([
        'email' => 'ana@example.com',
        'password' => Hash::make('parola-secreta'),
    ]);

    $this->postJson('/api/v1/login', [
        'email' => 'ana@example.com',
        'password' => 'parola-secreta',
    ])
        ->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
});

it('rejects bad credentials as 422 without leaking which field', function () {
    User::factory()->create([
        'email' => 'ana@example.com',
        'password' => Hash::make('parola-secreta'),
    ]);

    $this->postJson('/api/v1/login', [
        'email' => 'ana@example.com',
        'password' => 'gresit',
    ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['email']]);
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/logout')
        ->assertOk();

    expect($user->tokens()->count())->toBe(0);
});

it('rejects an unauthenticated call to a protected route with 401', function () {
    $this->getJson('/api/v1/user')->assertUnauthorized();
});
