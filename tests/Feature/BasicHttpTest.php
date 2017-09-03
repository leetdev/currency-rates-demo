<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Test basic application functionality
class BasicHttpTest extends TestCase
{

    public function test_index_redirect_to_login_page()
    {
        $response = $this->get('/');
        $response->assertStatus(302)
            ->assertRedirect('/login');

        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_login_page_content()
    {
        $response = $this->get('/login');
        $response->assertSee('Currency Exchange Rates');
    }

    public function test_oauth_provider_not_configured()
    {
        $response = $this->get('/login/dummy');
        $response->assertStatus(404);
    }

    public function test_logout_redirect_to_login_page()
    {
        $response = $this->get('/logout');
        $response->assertStatus(302)
            ->assertRedirect('/login');
    }
}
