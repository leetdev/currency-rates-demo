<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;

class LoginController extends Controller
{

    public function login($provider = null)
    {
        // Display login page
        if (!$provider) {
            return view('login');
        }

        // Redirect to provider authentication page
        return Socialite::driver($provider)->redirect();
    }

    public function auth($provider) {
        $user = Socialite::driver($provider)->user();
        return response()->json($user);
    }


    /**
     * Redirect the user to the Socialite provider authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Socialite provider.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('google')->user();

        // $user->token;
    }

}
