<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;


    // TODO:
    public function test_auth_callback_test1()
    {
        $this->assertTrue(true);
    }
}
