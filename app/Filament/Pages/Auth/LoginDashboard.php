<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class LoginDashboard extends Page
{
    protected static string $view = 'filament.pages.auth.login-dashboard';
    protected static ?string $navigationIcon = null;
    public $nik = '';
    public $password = '';
    public $remember = false;
    public $error = '';

    public function login()
    {
        $credentials = [
            'nik' => $this->nik,
            'password' => $this->password,
        ];
        if (Auth::attempt($credentials, $this->remember)) {
            Filament::auth()->login(Auth::user(), $this->remember);
            return redirect()->intended(Filament::getUrl());
        } else {
            $this->error = 'NIK atau password salah.';
        }
    }

    public function mount()
    {
        if (Filament::auth()->check()) {
            return redirect(Filament::getUrl());
        }
    }
}
