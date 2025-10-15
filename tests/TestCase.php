<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** @var object */
    public $user;

    /** @var object */
    public $member;

    /** @var object */
    public $admin;

    /** @var string|null */
    public $accessToken;

    /** @var object */
    public $payload;
}
