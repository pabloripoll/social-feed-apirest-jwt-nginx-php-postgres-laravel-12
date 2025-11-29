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

    /** @var object|null */
    public ?object $user = null;

    /** @var object|null */
    public ?object $member = null;

    /** @var object|null */
    public ?object $admin = null;

    /** @var string|null */
    public ?string $accessToken = null;

    /** @var object|null */
    public ?object $payload = null;
}
