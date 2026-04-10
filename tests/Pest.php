<?php

use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in(
    'Feature',
    'Unit',
    '../app/Domain/Geo/Tests',
    '../app/Domain/User/Tests',
    '../app/Domain/Admin/Tests',
    '../app/Domain/Member/Tests',
    '../app/Domain/Feed/Tests',
);

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

beforeAll(function () {
    // Run the migrations
    Artisan::call('migrate');

    // Run all seeders
    Artisan::call('db:seed');
});

afterAll(function () {
    // Rollback the migrations
    Artisan::call('migrate:rollback');
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
|
|
*/

/**
 * Get the current test instance with proper type hinting
 * Avoids undefined property define, e.g.: $this->something or PEST functions $this->post()
 * @disregard P1014 P1013 etc
 * @phpstan-ignore-next-line see: https://github.com/phpstan/phpstan/issues/10302
 *
 * @return TestCase
 */
function tpest(): TestCase
{
    /** @var TestCase $this */
    return test();
}

/**
 * Admin Login TestCase instance.
 *
 * email and password params are editables for test cases
 *
 * @param  array<string,mixed>  $payload
 */
function defAdminLogin(TestCase $test, array $overrides = []): TestResponse
{
    $admin = User::query()
        ->where('email', 'admin@example.com')
        ->with(['adminProfile'])
        ->first();

    $route = route('api-v1.admin-account.login');

    $payload = array_merge([
        'email' => $admin->email,
        'password' => '12345678aZ!',
    ], $overrides);

    return test()->post($route, $payload);
}

/**
 * Member Login TestCase instance.
 *
 * email and password params are editables for test cases
 *
 * @param  array<string,mixed>  $payload
 */
function defMemberLogin(TestCase $test, array $overrides = []): TestResponse
{
    $member = User::query()
        ->where('email', 'member@example.com')
        ->with(['memberProfile'])
        ->first();

    $route = route('api-v1.member-account.login');

    $payload = array_merge([
        'email' => $member->email,
        'password' => '12345678aZ!',
    ], $overrides);

    return test()->post($route, $payload);
}

/**
 * Fake JWT.
 *
 * *should be improved with wrong token paramenters*
 */
function fakeJWT(): string
{
    //return 'xyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiaWF0IjoxNzY1MzY5OTEyLCJleHAiOjE3NjUzNzUzMTIsIm5iZiI6MTc2NTM2OTkxMiwianRpIjoiTEhYZHJadzRYUUxjZVo4QiIsInN1YiI6IjEiLCJwcnYiOiJkZjZjYjdlMDg0NmY3YTZmYjc4OTQ5ZDRhN2I0YzBjYmRjYjE4YTc4Iiwicm9sZSI6IlJPTEVfTUVNQkVSIn0.E2dOATFAzwT1YzeFc9aq24AF_bDogXLUAudeCH7M5Lc';
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'sub' => fake()->uuid(),
        'iat' => time(),
        'exp' => time() + 3600,
    ]));
    $signature = base64_encode(fake()->sha256());

    return "{$header}.{$payload}.{$signature}";
}
