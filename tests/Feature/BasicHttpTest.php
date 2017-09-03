<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    public function test_oauth_provider_login_redirect()
    {
        // NOTE: this assumes that at least Google login is configured.
        // If you're gonna replace it, then change this to your default provider
        $provider = $google;

        $response = $this->get("/login/$provider");
        $response->assertStatus(302);
    }

    public function test_oauth_provider_not_configured()
    {
        $response = $this->get('/login/dummy');
        $response->assertStatus(404);
    }
}
