<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // Default OAuth provider info. Change if necessary.
    // Note that this doesn't have to necessarily be the "default" one,
    // but if you remove it entirely from services config your tests will fail.
    CONST DEFAULT_PROVIDER = 'google';
    CONST DEFAULT_PROVIDER_AUTH_URL_FRAGMENT = 'https://accounts.google.com/o/oauth2/auth?client_id';
    CONST DEFAULT_PROVIDER_CLASS = \Laravel\Socialite\Two\GoogleProvider::class;
}
