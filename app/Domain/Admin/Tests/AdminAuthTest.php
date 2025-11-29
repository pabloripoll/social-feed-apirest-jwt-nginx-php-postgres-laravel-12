<?php
/** @var \Tests\TestCase $this */

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Domain\Admin\Models\Admin;

beforeEach(function () {
    Artisan::call('db:seed');

    $email = fake()->unique()->safeEmail();
    $this->payload = (object) [
        'email' => $email,
        'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($email, '@', true)),
        'password' => '12345678aZ!',
    ];
});

describe('Default admin login success - @POST /api/v1/admin/auth/login', function () {
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

describe('Default admin login failed - @POST /api/v1/admin/auth/login', function () {
    it('fails as no admin user can login through auth controller', function () {
        $response = defAdminLogin($this, ['email' => 'member@example.com']);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->etc()
            );
    });
});

describe('Admin user registration fail - @POST /api/v1/admin/auth/register', function () {
    it('fails that an admin user can register another admin user because of wrong nickname', function () {
        $defAdmin = defAdminLogin($this);
        $route = route('api-v1.admin-auth.register');

        $payload = [
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
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
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'nickname')
                ->etc()
            );
    });
});

describe('Admin user registration fail - @POST /api/v1/admin/auth/register', function () {
    it('fails that an admin user can register another admin user because of wrong email', function () {
        $defAdmin = defAdminLogin($this);
        $route = route('api-v1.admin-auth.register');

        $payload = [
            'nickname' => $this->payload->nickname,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
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
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'email')
                ->etc()
            );
    });
});

describe('Admin user registration fail - @POST /api/v1/admin/auth/register', function () {
    it('fails that an admin user can register another admin user because of wrong password', function () {
        $defAdmin = defAdminLogin($this);
        $route = route('api-v1.admin-auth.register');
        // missing password field
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload, [
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
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
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
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
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
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
            'Authorization' => "Bearer " . $defAdmin->json()['token'],
        ]);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
    });
});

describe('Admin user login fail - @POST /api/v1/admin/auth/login', function () {
    it('fails when user login input is a wrong password', function () {
        $admin = Admin::factory()->create();
        $route = route('api-v1.admin-auth.login');
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

describe('Admin user login success- @POST /api/v1/admin/auth/login', function () {
    it('succeeds that a user can log into its account', function () {
        $admin = Admin::factory()->create();
        $route = route('api-v1.admin-auth.login');
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

describe('Admin auth token refresh fail - @POST /api/v1/admin/auth/refresh', function () {
    it('fails because authentication token is not found on access logs', function () {
        Admin::factory()->withAuth()->create();
        $wrongJwt = Str::random(64);
        $route = route('api-v1.admin-auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $wrongJwt",
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_invalid')
                ->etc()
            );
    });
});

describe('Admin auth token refresh fail - @POST /api/v1/admin/auth/refresh', function () {
    it('fails because authentication token is terminated and cannot be refreshed', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $accessLog->is_terminated = true;
        $accessLog->save();
        $route = route('api-v1.admin-auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_invalid')
                ->etc()
            );
    });
});

describe('Admin auth token refresh success - @POST /api/v1/admin/auth/refresh', function () {
    it('succeeds authentication token can be refreshed', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $accessLog->is_expired = true;
        $accessLog->expires_at = now();
        $accessLog->save();
        $route = route('api-v1.admin-auth.refresh');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('token')
                ->has('token_expired')
                ->has('expires_in')
                ->etc()
            );
    });
});

describe('Admin logout success - @POST /api/v1/admin/auth/logout', function () {
    it('succeeds user authentication can logout', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $accessLog->is_expired = true;
        $accessLog->expires_at = now();
        $accessLog->save();
        $route = route('api-v1.admin-auth.logout');
        $response = $this->post($route, [], [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_ACCEPTED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('token_expired')
                ->etc()
            );
    });
});

describe('Admin whoami fail - @GET /api/v1/admin/auth/whoami', function () {
    it('fails user cannot see its account main properties if there is no JWT', function () {
        $route = route('api-v1.admin-auth.whoami');
        $response = $this->get($route);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'token_not_provided')
                ->etc()
            );
    });
});

describe('Admin whoami fail - @GET /api/v1/admin/auth/whoami', function () {
    it('fails user cannot see its account main properties JWT is terminated by authentication logout', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load('user.adminAccessLogs');
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();
        $accessLog->is_terminated = true;
        $accessLog->save();
        $route = route('api-v1.admin-auth.whoami');
        $response = $this->get($route, [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('message', fn ($message) => is_string($message))
                ->where('error', fn ($error) => $error === 'token_terminated')
                ->etc()
            );
    });
});

describe('Admin whoami success - @GET /api/v1/admin/auth/whoami', function () {
    it('succeeds user authenticated can see itself', function () {
        $admin = Admin::factory()->withAuth()->create();
        $admin->load(['profile', 'user.adminAccessLogs']);
        $accessLog = $admin->user->adminAccessLogs()->latest()->first();

        $route = route('api-v1.admin-auth.whoami');
        $response = $this->get($route, [
            'Authorization' => "Bearer $accessLog->token",
        ]);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('email', fn ($email) => $email === $admin->user->email)
                ->where('uid', fn ($uid) => $uid === $admin->uid)
                ->where('nickname', fn ($nickname) => $nickname === $admin->profile->nickname)
                ->etc()
            );
    });
});
