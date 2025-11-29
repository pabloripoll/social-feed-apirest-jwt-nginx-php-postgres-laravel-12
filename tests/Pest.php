<?php
/** @var \Tests\TestCase $this */

use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\User\Models\User;

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
    '../app/Domain/Admin/Tests',
    '../app/Domain/Member/Tests',
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
 * Admin Login TestCase instance.
 *
 * email and password params are editables for test cases
 *
 * @param Tests\TestCase $test
 * @param array<string,mixed> $payload
 * @return \Illuminate\Testing\TestResponse
 */
function defAdminLogin(TestCase $test, array $overrides = []): TestResponse
{
    $admin = User::query()
        ->where('email', 'admin@example.com')
        ->with(['adminProfile'])
        ->first();

    $route = route('api-v1.admin-auth.login');

    $payload = array_merge([
        'email' => $admin->email,
        'password' => '12345678aZ!',
    ], $overrides);

    return $test->post($route, $payload);
}

/**
 * Member Login TestCase instance.
 *
 * email and password params are editables for test cases
 *
 * @param Tests\TestCase $test
 * @param array<string,mixed> $payload
 * @return \Illuminate\Testing\TestResponse
 */
function defMemberLogin(TestCase $test, array $overrides = []): TestResponse
{
    $member = User::query()
        ->where('email', 'member@example.com')
        ->with(['memberProfile'])
        ->first();

    $route = route('api-v1.member-auth.login');

    $payload = array_merge([
        'email' => $member->email,
        'password' => '12345678aZ!',
    ], $overrides);

    return $test->post($route, $payload);
}
