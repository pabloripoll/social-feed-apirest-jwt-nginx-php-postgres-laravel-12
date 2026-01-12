<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property object|null $user
 * @property object|null $member
 * @property object|null $admin
 * @property string|null $accessToken
 * @property object|null $payload
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public ?object $user = null;

    public ?object $member = null;

    public ?object $admin = null;

    public ?string $accessToken = null;

    public ?object $payload = null;
}
