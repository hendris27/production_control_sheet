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

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown
    placement="bottom-end"
    :attributes="\Filament\Support\prepare_inherited_attributes($attributes)->class(['fi-user-menu'])"
>
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="shrink-0"
        >
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

        @if ($hasProfileItem)
            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item
                    :color="$profileItem?->getColor()"
                    :icon="$profileItem?->getIcon() ??
                        (\Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ??
                            'heroicon-m-user-circle')"
                    :href="$profileItemUrl ?? filament()->getProfileUrl()"
                    :target="$profileItem?->shouldOpenUrlInNewTab() ?? false ? '_blank' : null"
                    tag="a"
                >
                    {{ $profileItem?->getLabel() ?? (($profilePage ? $profilePage::getLabel() : null) ?? filament()->getUserName($user)) }}
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        @else
            <x-filament::dropdown.header
                :color="$profileItem?->getColor()"
                :icon="$profileItem?->getIcon() ??
                    (\Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ??
                        'heroicon-m-user-circle')"
            >Welcome!
                {{ $profileItem?->getLabel() ?? filament()->getUserName($user) }}
            </x-filament::dropdown.header>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if (filament()->hasDarkMode() && !filament()->hasDarkModeForced())
        <x-filament::dropdown.list>
            <x-filament-panels::theme-switcher />
        </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
            @php
                $itemPostAction = $item->getPostAction();
            @endphp

            <x-filament::dropdown.list.item
                :action="$itemPostAction"
                :color="$item->getColor()"
                :href="$item->getUrl()"
                :icon="$item->getIcon()"
                :method="filled($itemPostAction) ? 'post' : null"
                :tag="filled($itemPostAction) ? 'form' : 'a'"
                :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null"
            >
                {{ $item->getLabel() }}
            </x-filament::dropdown.list.item>
        @endforeach

        {{-- Logout Button with Modal --}}
        @if ($logoutItem)
            <x-filament::dropdown.list.item
                :color="$logoutItem?->getColor() ?? 'danger'"
                :icon="$logoutItem?->getIcon() ?? 'heroicon-o-arrow-right-on-rectangle'"
                x-data="{}"
                x-on:click="$dispatch('open-modal', { id: 'logout-modal' })"
            >
                {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
            </x-filament::dropdown.list.item>
        @else
            <x-filament::dropdown.list.item
                color="danger"
                icon="heroicon-o-arrow-right-on-rectangle"
                x-data="{}"
                x-on:click="$dispatch('open-modal', { id: 'logout-modal' })"
            >
                {{ __('filament-panels::layout.actions.logout.label') }}
            </x-filament::dropdown.list.item>
        @endif
    </x-filament::dropdown.list>
</x-filament::dropdown>

{{-- Logout Confirmation Modal --}}
<x-filament::modal
    id="logout-modal"
    icon="heroicon-o-exclamation-triangle"
    icon-color="danger"
    alignment="center"
    width="sm"
>
    <x-slot name="heading">
        Logout Confirmation!
    </x-slot>
    <x-slot name="description">
        Are you sure you want to log out of the system?
    </x-slot>

    <x-slot name="footerActions">
        <div class="flex w-full justify-center gap-4">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'logout-modal' })"
            >
                Cancel
            </x-filament::button>
            <form
                method="POST"
                action="{{ $logoutItem?->getUrl() ?? filament()->getLogoutUrl() }}"
                class="inline"
            >
                @csrf
                <x-filament::button
                    color="danger"
                    type="submit"
                >
                    Yes, Logout
                </x-filament::button>
            </form>
        </div>
    </x-slot>
</x-filament::modal>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}
