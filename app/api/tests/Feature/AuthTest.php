<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertObjectHasProperty;

it('allows a user to sign up', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();

  $response = $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
  ]);

  $response->assertStatus(201);

  $this->assertDatabaseHas('users', [
    'email' => $email,
  ]);
});

it('returns user on successful sign up', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();

  $response = $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
  ]);

  $response->assertJsonStructure([
    'user' => [
      'id',
      'name',
      'email'
    ]
  ]);
});

it('rejects signup with existing email', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();

  $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
  ]);

  $response = $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
  ]);

  $response->assertStatus(422);
  $response->assertJsonValidationErrors(['email']);
});

it('rejects signup if password does not match confirmation', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();

  $response = $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password12345',
  ]);

  $response->assertStatus(422);
  $response->assertJsonValidationErrors(['password']);
});

it('hashes the password on signup', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();
  $password = 'password1234';

  $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
  ]);

  $user = User::where('email', $email)->first();

  $this->assertNotEquals($password, $user->password,);
  $this->assertTrue(Hash::check($password, $user->password));
});

it('rejects signup if password too short', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();
  $password = 'password';

  $response = $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
  ]);

  $response->assertStatus(422);
  $response->assertJsonValidationErrors(['password']);
});

it('rejects signup if name is too long', function () {
  /** @var \Tests\TestCase $this */
  $name = str_repeat('a', 256);
  $email = fake()->unique()->safeEmail();
  $password = 'password1234';

  $response = $this->postJson('/api/signup', [
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
  ]);

  $response->assertStatus(422);
  $response->assertJsonValidationErrors(['name']);
});

it('rejects signup if email invalid', function () {
  /** @var \Tests\TestCase $this */
  $email = 'dan';
  $password = 'password1234';

  $response = $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
  ]);

  $response->assertStatus(422);
  $response->assertJsonValidationErrors(['email']);
});

it('rejects if name field missing', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();
  $password = 'password1234';

  $response = $this->postJson('/api/signup', [
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
  ]);

  $response->assertStatus(422);
  $response->assertJsonValidationErrors(['name']);
});

it('rejects if email field missing', function () {
  /** @var \Tests\TestCase $this */
  $password = 'password1234';

  $response = $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'password' => $password,
    'password_confirmation' => $password,
  ]);

  $response->assertStatus(422);
  $response->assertJsonValidationErrors(['email']);
});

it('only creates a single user on signup', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();

  $this->postJson('/api/signup', [
    'name' => fake()->name(),
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
  ]);

  $this->assertDatabaseCount('users', 1);
});

// it('ensures names are not just numbers', function () {
//   /** @var \Tests\TestCase $this */
//   $email = fake()->unique()->safeEmail();
// 
//   $response = $this->postJson('/api/signup', [
//     'name' => '123456789',
//     'email' => $email,
//     'password' => 'password1234',
//     'password_confirmation' => 'password1234',
//   ]);
// 
//   $response->assertJsonValidationErrors(['name']);
// });

it('enables use of international characters in names', function () {
  /** @var \Tests\TestCase $this */
  $email = fake()->unique()->safeEmail();
  $email2 = fake()->unique()->safeEmail();

  $this->postJson('/api/signup', [
    'name' => 'Ólafur Björnsson',
    'email' => $email,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
  ]);

  $this->postJson('/api/signup', [
    'name' => 'محمد',
    'email' => $email2,
    'password' => 'password1234',
    'password_confirmation' => 'password1234',
  ]);

  $this->assertDatabaseCount('users', 2);
});
