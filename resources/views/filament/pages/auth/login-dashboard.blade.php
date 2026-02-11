@extends('layouts.app')

<!-- Panggil CSS dan JS langsung dari hasil build agar bisa diakses dari device lain -->
<link
    rel="stylesheet"
    href="{{ asset('build/assets/app-B6eci5Kr.css') }}"
>
<script
    src="{{ asset('build/assets/app-Bj43h_rG.js') }}"
    defer
></script>

@section('content')
    <div class="flex min-h-screen flex-col md:flex-row">
        <!-- Kiri: Gambar atau background, hanya tampil di md ke atas -->
        <div
            id="left-panel"
            class="tablet:hidden hidden min-h-screen w-[60%] items-center justify-center bg-cover bg-center md:flex"
            style="background-image: url('{{ url('storage/photo/bg-production2.png') }}'); background-size:contain; background-repeat:no-repeat;
    background-position: center; height: 80vh;"
        >
            <!-- Bisa tambahkan logo besar atau ilustrasi di sini jika mau -->
        </div>
        <style>
            @media (max-width: 700px) {
                .tablet\:hidden {
                    display: none !important;
                }
            }

            /* Hide left panel on any portrait device so tablets show only the form */
            @media (orientation: portrait) {
                #left-panel {
                    display: none !important;
                }

                #right-panel {
                    width: 100% !important;
                    min-height: 100vh !important;
                }

                #right-panel .max-w-md {
                    max-width: 420px !important;
                    width: 100% !important;
                }
            }

            /* When device is portrait and at least 1200x1920 (or high DPR tablets), extra safety rules */
            @media (orientation: portrait) and (min-width: 1200px) and (min-height: 1920px),
            (orientation: portrait) and (min-device-width: 1200px) and (min-device-height: 1920px),
            /* Catch high-DPR tablets (e.g. 1200x1920 @2dpr -> CSS 600x960): use CSS width threshold + tall aspect */
            (orientation: portrait) and (min-width: 600px) and (min-aspect-ratio: 3/2) {
                #left-panel {
                    display: none !important;
                }

                #right-panel {
                    width: 100% !important;
                    min-height: 100vh !important;
                }

                #right-panel .max-w-md {
                    max-width: 420px !important;
                    width: 100% !important;
                }
            }
        </style>
        <!-- Kanan: Form Login, full width di mobile/tablet -->
        <div
            id="right-panel"
            class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-purple-600 via-blue-600 via-blue-600 to-purple-900 to-purple-900 px-6 py-6 md:w-[40%]"
        >
            <div
                class="bg-green flex w-full max-w-md flex-col items-center rounded-xl border border-white border-opacity-20 p-6 shadow-lg">
                <img
                    src="{{ url('storage/photo/Siix.png') }}"
                    alt="Logo"
                    class="mb-2 h-20 w-32 object-contain"
                    onerror="this.onerror=null; this.src='{{ url('storage/photo/Siix.jpg') }}'"
                >

                <p class="mb-6 text-center text-[20px] font-bold text-white">Input Your NIK and Your Password</p>
                @if ($error)
                    <div class="mb-4 w-full text-center text-sm text-red-600">{{ $error }}</div>
                @endif
                @if (session('csrf_error'))
                    <div class="mb-4 w-full text-center text-sm text-red-600">{{ session('csrf_error') }}</div>
                @endif
                <form
                    method="POST"
                    action="{{ route('admin.login') }}"
                    class="flex w-full flex-col gap-4"
                >
                    @csrf
                    <div>
                        <label
                            for="nik"
                            class="mb-1 block font-semibold text-gray-800"
                        >NIK</label>
                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            value="{{ old('nik') }}"
                            class="@error('nik') border-red-500  @enderror w-full rounded-lg border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            required
                            autofocus
                        >
                        @error('nik')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="relative">
                        <label
                            for="password"
                            class="mb-1 block font-semibold text-gray-800"
                        >Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="@error('password') border-red-500 @enderror w-full rounded-lg border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            required
                        >
                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-9 text-blue-500 focus:outline-none"
                        >
                            <span id="showIcon">
                                <!-- Eye SVG -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    width="22"
                                    height="22"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                            </span>
                            <span
                                id="hideIcon"
                                style="display:none;"
                            >
                                <!-- Eye Off SVG -->
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    width="22"
                                    height="22"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.442-4.362M6.634 6.634A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.96 9.96 0 01-4.132 5.255M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 3l18 18"
                                    />
                                </svg>
                            </span>
                        </button>
                        @error('password')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="mb-4 mt-4 w-full rounded-lg bg-blue-700 py-2 font-bold text-white ring-2 ring-blue-700/30 transition hover:bg-green-600"
                    >Login</button>
                </form>
                <script>
                    function togglePassword() {
                        var passwordInput = document.getElementById('password');
                        var showIcon = document.getElementById('showIcon');
                        var hideIcon = document.getElementById('hideIcon');
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            showIcon.style.display = 'none';
                            hideIcon.style.display = 'inline';
                        } else {
                            passwordInput.type = 'password';
                            showIcon.style.display = 'inline';
                            hideIcon.style.display = 'none';
                        }
                    }
                </script>
            </div>
        </div>
    </div>
@endsection
