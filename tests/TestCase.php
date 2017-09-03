<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // Default OAuth provider. Change if necessary
    CONST DEFAULT_PROVIDER = 'google';
}
