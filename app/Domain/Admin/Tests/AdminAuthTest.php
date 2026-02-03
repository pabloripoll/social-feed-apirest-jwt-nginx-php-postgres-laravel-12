<?php

use App\Domain\Admin\Models\Admin;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    /** @var \Tests\TestCase $this */

    Artisan::call('db:seed');

    $email = fake()->unique()->safeEmail();
    $this->payload = (object) [
        'email' => $email,
        'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($email, '@', true)),
        'password' => '12345678aZ!',
    ];
});

describe('Default admin login success - @POST /api/v1/admin/account/login', function () {
    it('succeeds that a default admin user can log into its account to test new admin auth controller', function () {
        $response = defAdminLogin($this);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('token', fn ($token) => is_string($token))
                ->where('expires_in', fn ($expires_in) => is_int($expires_in))
                ->etc()
            );
    });
});

describe('Default admin login failed - @POST /api/v1/admin/account/login', function () {
    it('fails as no admin user can login through auth controller', function () {
        $response = defAdminLogin($this, ['email' => 'member@example.com']);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->etc()
            );
    });
});

describe('Admin user registration fail - @POST /api/v1/admin/account/register', function () {
    it('fails that an admin user can register another admin user because of wrong nickname', function () {
        /** @var \Tests\TestCase $this */
        $defAdmin = defAdminLogin($this);
        $route = route('api-v1.admin-account.register');

        $payload = [
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'nickname')
                ->etc()
            );

        $payload = [
            'nickname' => $this->payload->nickname.'@',
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'nickname')
                ->etc()
            );
    });
});

describe('Admin user registration fail - @POST /api/v1/admin/account/register', function () {
    it('fails that an admin user can register another admin user because of wrong email', function () {
        /** @var \Tests\TestCase $this */
        $defAdmin = defAdminLogin($this);
        $route = route('api-v1.admin-account.register');

        $payload = [
            'nickname' => $this->payload->nickname,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'email')
                ->etc()
            );

        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => '@'.$this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'email')
                ->etc()
            );
    });
});

describe('Admin user registration fail - @POST /api/v1/admin/account/register', function () {
    it('fails that an admin user can register another admin user because of wrong password', function () {
        /** @var \Tests\TestCase $this */
        $defAdmin = defAdminLogin($this);
        $route = route('api-v1.admin-account.register');
        // missing password field
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
        // missing password_confirmation field
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
        // password confirmation mismatch
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password.'?',
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
        // password confirmation no comply
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => '1234aZ!',
            'password' => '1234aZ!',
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => 'Bearer '.$defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
    });
});

describe('Admin user login fail - @POST /api/v1/admin/account/login', function () {
    it('fails when user login input is a wrong password', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->create();
        $route = route('api-v1.admin-account.login');
        $payload = [
            'email' => $admin->user->email,
            'password' => 'wrong-password',
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->etc()
            );
    });
});

describe('Admin user login success- @POST /api/v1/admin/account/login', function () {
    it('succeeds that a user can log into its account', function () {
        /** @var \Tests\TestCase $this */
        $admin = Admin::factory()->create();
        $route = route('api-v1.admin-account.login');
        $payload = [
            'email' => $admin->user->email,
            'password' => 'password',
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('token', fn ($token) => is_string($token))
                ->where('expires_in', fn ($expires_in) => is_int($expires_in))
                ->etc()
            );
    });
});
