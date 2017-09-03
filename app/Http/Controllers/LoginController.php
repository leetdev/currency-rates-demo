<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Socialite;

use App\Http\Controllers\Controller;
use App\SocialAuth;
use App\Exceptions\SocialAuthException;

class LoginController extends Controller
{
    // Services
    protected $socialAuth;

    // Controller constructor
    public function __construct(SocialAuth $socialAuth)
    {
        // Services
        $this->socialAuth = $socialAuth;

        // Middleware
        $this->middleware('guest')->except('logout');
    }

    // Login entry point / oauth redirector
    public function login($provider = null)
    {
        // Display login page
        if (!$provider) {
            return view('login');
        }

        // Redirect to provider authentication page
        return $this->socialAuth->redirect($provider);
    }

    // Authentication callbacks get routed through here
    public function auth($provider)
    {
        try {
            // Process callback by our social auth service
            $this->socialAuth->login($provider);

            // Redirect to main page
            return redirect('/');

        } catch (SocialAuthException $e) {
            // In case of authentication error, go back to login page
            return redirect('login')
                ->with('alert', 'danger')
                ->with('flash', $e->getMessage());
        }
    }

    // Logout
    public function logout()
    {
        Auth::logout();
        return redirect('login')
            ->with('alert', 'success')
            ->with('flash', 'You have been successfully logged out.');
    }
}
