
{{-- Pastikan Tailwind CSS ter-load di semua halaman Filament, termasuk komponen ini --}}
@php
    $user = filament()->auth()->user();
    $items = filament()->getUserMenuItems();

    $profileItem = $items['profile'] ?? ($items['account'] ?? null);
    $profileItemUrl = $profileItem?->getUrl();
    $profilePage = filament()->getProfilePage();
    $hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

    $logoutItem = $items['logout'] ?? null;

    $items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp



{{-- Debug: Tampilkan warning jika Tailwind tidak ter-load --}}
<div id="tailwind-debug-warning" style="display:none;color:red;font-weight:bold;">TIDAK ADA TAILWIND! Cek pemuatan CSS di layout utama.</div>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        var test = document.createElement('div');
        test.className = 'hidden';
        document.body.appendChild(test);
        var isTailwind = window.getComputedStyle(test).display === 'none';
        if (!isTailwind) {
            document.getElementById('tailwind-debug-warning').style.display = 'block';
        }
        document.body.removeChild(test);
    });
</script>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown placement="bottom-end" :teleport="false" :attributes="\Filament\Support\prepare_inherited_attributes($attributes)->class(['fi-user-menu'])">
    <x-slot name="trigger">
        <button aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}" type="button"
            class="shrink-0" >
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
        {{ \Filament\Support\Facade... }}
    @endif


    <form method="post" action="{{ $logoutItem?->getUrl() ?? filament()->getLogoutUrl() }}" class="px-4 py-2">
        @csrf
        <button type="submit" class="w-full text-left bg-transparent border-0 p-0 m-0">
            {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
        </button>
    </form>
