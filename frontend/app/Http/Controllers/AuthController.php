<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show login page.
     */
    public function showLogin()
    {
        if (Session::has('api_token')) {
            return redirect('/dashboard');
        }
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle login request → call backend API.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $apiBase = config('services.api.base_url');

        $response = Http::acceptJson()->post("{$apiBase}/auth/login", [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        if ($response->failed()) {
            return back()->withErrors([
                'login' => $response->json('message') ?? 'Email atau password salah.',
            ]);
        }

        $data = $response->json();

        Session::put('api_token', $data['token'] ?? $data['access_token'] ?? '');
        Session::put('auth_user', $data['user'] ?? []);

        return redirect('/dashboard');
    }

    /**
     * Logout: destroy session.
     */
    public function logout()
    {
        $apiBase = config('services.api.base_url');
        $token   = Session::get('api_token');

        Http::withToken($token)->post("{$apiBase}/auth/logout");

        Session::flush();

        return redirect('/login');
    }
}
