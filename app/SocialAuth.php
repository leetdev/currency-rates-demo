<?php
namespace App;

use Exception;
use Socialite;

use App\Exceptions\SocialAuthException;

class SocialAuth
{
    // Redirects user to provider login page
    public function redirect($provider)
    {
        // Verify that the provider is actually configured
        if (!config("services.$provider.client_id")) {
            abort(404);
        }

        // Redirect to provider
        return Socialite::driver($provider)->redirect();
    }

    // This is the login callback action for auth provider
    public function login($provider)
    {
        try {
            // Retrieve user info from provider
            $social = Socialite::driver($provider)->user();

            // Register (if needed) and fetch user
            $user = User::firstOrCreate(
                [
                    'provider'      => $provider,
                    'provider_id'   => $social->getId(),
                ], [
                    'name'          => $social->getName(),
                    'email'         => $social->getEmail(),
                    'avatar'        => $social->getAvatar(),
                ]
            );

            // Login the user
            auth()->login($user);

        } catch (Exception $e) {
            throw new SocialAuthException("Failed to authenticate with $provider");
        }
    }
}
