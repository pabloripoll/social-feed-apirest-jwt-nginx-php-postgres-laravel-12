<?php

/** @var \Tests\TestCase $this */

use App\Domain\Member\Models\Member;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\JsonResponse;

beforeEach(function () {
    Artisan::call('db:seed');

    $email = fake()->unique()->safeEmail();
    $this->payload = (object) [
        'email' => $email,
        'nickname' => preg_replace('/[^A-Za-z0-9]/', '', strstr($email, '@', true)),
        'password' => '12345678aZ!',
    ];
});

describe('Member user registration fail - @POST /api/v1/account/register', function () {
    it('fails that a user can register because of wrong nickname', function () {
        $route = route('api-v1.member-account.register');
        $payload = [
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
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
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'nickname')
                ->etc()
            );
    });
});

describe('Member user registration fail - @POST /api/v1/account/register', function () {
    it('fails that a user can register because of wrong email', function () {
        $route = route('api-v1.member-account.register');
        $payload = [
            'nickname' => $this->payload->nickname,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
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
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'email')
                ->etc()
            );
    });
});

describe('Member user registration fail - @POST /api/v1/account/register', function () {
    it('fails that a user can register because of wrong password', function () {
        $route = route('api-v1.member-account.register');
        // missing password field
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
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
        $response = $this->post($route, $payload);
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
        $response = $this->post($route, $payload);
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
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_NOT_ACCEPTABLE)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('message')
                ->where('error', fn ($error) => $error === 'password')
                ->etc()
            );
    });
});

describe('Member user registration success - @POST /api/v1/account/register', function () {
    it('succeeds that a user can register by itself as a member', function () {
        $route = route('api-v1.member-account.register');
        $payload = [
            'nickname' => $this->payload->nickname,
            'email' => $this->payload->email,
            'password' => $this->payload->password,
            'password_confirmation' => $this->payload->password,
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('email')
                ->has('nickname')
                ->where('uid', fn ($uid) => is_int($uid))
                ->etc()
            );
    });
});

describe('Member user login fail - @POST /api/v1/account/login', function () {
    it('fails when user login input is a wrong password', function () {
        $member = Member::factory()->create();
        $route = route('api-v1.member-account.login');
        $payload = [
            'email' => $member->user->email,
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

describe('Member user login success- @POST /api/v1/account/login', function () {
    it('succeeds that a user can log into its account', function () {
        $member = Member::factory()->create();
        $route = route('api-v1.member-account.login');
        $payload = [
            'email' => $member->user->email,
            'password' => 'password',
        ];
        $response = $this->post($route, $payload);
        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('token', fn ($token) => is_string($token))
                ->where('expires_in', fn ($expires_in) => is_int($expires_in))
                ->etc()
            );
    });
});
