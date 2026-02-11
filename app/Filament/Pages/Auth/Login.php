<?php

namespace App\Filament\Pages\Auth;

// ---------------------------------------------
// ALUR LOGIN YANG AKTIF DI PROJECT INI:
// ---------------------------------------------
// 1. User mengakses halaman login di /admin/login
// 2. Route /admin/login diarahkan ke LoginController@showLoginForm
// 3. Controller menampilkan view login-dashboard.blade.php
// 4. User submit form login ke /admin/login (POST)
// 5. LoginController@login memproses autentikasi NIK & password
// 6. Jika sukses, redirect ke dashboard. Jika gagal, error tampil di form
// 7. Logout via /admin/logout (POST) ke LoginController@logout
//
// File ini (Login.php) adalah custom Filament login page, TIDAK digunakan
// dalam alur login utama saat ini. Login utama pakai controller & blade custom.
// ---------------------------------------------
// FILE INI: Login.php
// ---------------------------------------------
// Custom Filament login page. TIDAK digunakan dalam alur login utama.
// Login utama menggunakan controller dan blade custom.
// Untuk login utama, lihat LoginController dan login-dashboard.blade.php
// ---------------------------------------------
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms;

class Login extends BaseLogin
{
    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nik')
                ->label('NIK')
                ->required()
                ->autofocus()
                ->rule('string')
                ->text('black'),
            Forms\Components\TextInput::make('password')
                ->label(__('filament-panels::pages/auth/login.form.password.label'))
                ->password()
                ->required(),
            Forms\Components\Checkbox::make('remember')
                ->label(__('filament-panels::pages/auth/login.form.remember.label')),
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'nik' => $data['nik'],
            'password' => $data['password'],
        ];
    }
}
