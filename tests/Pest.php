<?php

use Tests\TestCase;
use App\Domain\User\Models\User;
use App\Models\LicenseSeat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use function Pest\Laravel\actingAs;

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
});

afterAll(function () {
    // Rollback the migrations
    Artisan::call('migrate:rollback');
});
