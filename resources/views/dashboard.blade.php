@extends('layouts.app')

@section('content')
<div class="min-h-screen flex bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-800 text-white flex flex-col py-8 px-4 shadow-xl">
        <div class="mb-8 flex items-center gap-2">
            <img src="{{ asset('storage/photo/siix.png') }}" alt="Logo" class="w-10 h-10 object-contain">
            <span class="font-bold text-lg">Admin Menu</span>
        </div>
        <nav class="flex-1">
            <ul class="space-y-4">
                <li><a href="/dashboard" class="block py-2 px-3 rounded hover:bg-blue-700">Dashboard</a></li>
                <li><a href="#" class="block py-2 px-3 rounded hover:bg-blue-700">Data Solder</a></li>
                <li><a href="#" class="block py-2 px-3 rounded hover:bg-blue-700">User</a></li>
                <li><a href="#" class="block py-2 px-3 rounded hover:bg-blue-700">Laporan</a></li>
            </ul>
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="w-full bg-red-600 py-2 rounded font-bold hover:bg-red-700 transition">Logout</button>
        </form>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-8 text-center w-full max-w-2xl">
            <h1 class="text-3xl font-bold text-blue-700 mb-4">Selamat Datang di Dashboard</h1>
            <p class="text-lg text-gray-700">Login berhasil. Ini adalah halaman menu utama admin dengan sidebar.</p>
        </div>
    </main>
</div>
@endsection
