<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ---------------------------------------------
// FILE INI: LoginController.php
// ---------------------------------------------
// Controller utama untuk proses login dan logout custom.
// - showLoginForm: Menampilkan form login (Blade custom)
// - login: Memproses autentikasi NIK & password
// - logout: Logout user
// Digunakan untuk route /admin/login dan /admin/logout
// ---------------------------------------------
class LoginController extends Controller
{
    public function showLoginForm()
    // Menampilkan halaman login custom (login-dashboard.blade.php) untuk /admin/login dan filament.admin.auth.login
    {
        if (request()->routeIs('admin.login') || request()->routeIs('filament.admin.auth.login')) {
            return view('filament.pages.auth.login-dashboard', [
                'error' => session('error') ?? null,
            ]);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    // Memproses login: validasi NIK & password, autentikasi, dan redirect/error
    {
        try {
            $credentials = $request->validate([
                'nik' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.login')
                ->with('error', 'Data login tidak valid.')
                ->withInput($request->only('nik', 'remember'));
        }

        if ($request->has('_token') && $request->session()->token() !== $request->_token) {
            return redirect()->route('admin.login')
                ->with('csrf_error', 'Session expired, silakan refresh halaman dan login ulang.');
        }

        if (Auth::attempt(['nik' => $credentials['nik'], 'password' => $credentials['password']], $request->filled('remember'))) {
            $request->session()->regenerate();
            // Redirect ke dashboard Filament
            return redirect()->intended(route('filament.admin.pages.dashboard'));
        }

        if ($request->routeIs('admin.login') || $request->routeIs('filament.admin.auth.login')) {
            return redirect()->route('admin.login')
                ->with('error', 'NIK atau password salah.')
                ->withInput($request->only('nik', 'remember'));
        }
        return back()->withErrors([
            'nik' => 'NIK atau password salah.',
        ])->withInput($request->only('nik', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Redirect ke halaman login custom (support admin.login dan filament.admin.auth.login)
        return redirect()->route('admin.login');
    }
}
