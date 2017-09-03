<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Socialite\Contracts\Factory as Socialite;

use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function mockSocialiteFacade($email = 'foo@bar.com', $token = 'foo', $id = 1, $name = 'Foo Bar')
    {
        $socialiteUser = $this->createMock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->token = $token;
        $socialiteUser->method('getId')->willReturn($id);
        $socialiteUser->method('getName')->willReturn($name);
        $socialiteUser->method('getEmail')->willReturn($email);

        $provider = $this->createMock($this::DEFAULT_PROVIDER_CLASS);
        $provider->expects($this->any())
            ->method('user')
            ->willReturn($socialiteUser);

        $stub = $this->createMock(Socialite::class);
        $stub->expects($this->any())
            ->method('driver')
            ->willReturn($provider);

        // Replace Socialite Instance with our mock
        $this->app->instance(Socialite::class, $stub);
    }

    public function test_oauth_login_redirects_correctly()
    {
        $provider = $this::DEFAULT_PROVIDER;

        $response = $this->get("/login/$provider");
        $response->assertStatus(302);
        $this->assertContains($this::DEFAULT_PROVIDER_AUTH_URL_FRAGMENT, $response->getTargetUrl());
    }

    public function test_oauth_provider_callback_redirects_to_home_page()
    {
        $provider = $this::DEFAULT_PROVIDER;
        $this->mockSocialiteFacade('foo@bar.com');

        $response = $this->get("/login/$provider/callback");
        $response->assertStatus(302);
        $this->assertEquals(url('/'), $response->getTargetUrl());
    }

    // TODO: authentication error redirects to login

}
