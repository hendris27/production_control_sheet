<div>
    @if (auth()->user() && auth()->user()->hasRole('admin'))
        <x-filament-widgets::widget>
            <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-paint-brush class="h-5 w-5" />
                    FCT Management System
                </div>
            </x-slot>

            <style>
                /* Custom dropdown */
                .dropdown-right {
                    position: absolute;
                    left: 100%;
                    top: 0;
                    margin-left: 0.5rem;
                    background: white;
                    border-radius: 0.5rem;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
                    width: 160px;
                    z-index: 50;
                }

                .dropdown-right li {
                    color: black !important;
                    transition: all 0.2s ease-in-out;
                }
            </style>

            <div class="relative w-full bg-amber-500 p-4">
                <!-- CARD -->
                <div class="flex gap-4">
                    <!-- FCT Machine Button -->
                    <div
                        class="relative"
                        x-data="{ open: false }"
                    >
                        <x-filament::button
                            color="primary"
                            size="sm"
                            class="w-44"
                            @click="open = !open"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <x-heroicon-s-cog class="h-5 w-5" />
                                FCT Machine
                            </div>
                        </x-filament::button>

                        <!-- DROPDOWN MENU KE SAMPING -->
                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="dropdown-right"
                        >
                            <ul class="divide-y divide-gray-200 rounded-lg bg-white shadow-lg">
                                <li>
                                    <a
                                        href="{{ route('filament.admin.resources.register-f-c-ts.create') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        Register
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('filament.admin.pages.checksheet-page') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        Storing
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('filament.admin.pages.checksheet-page') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        Running
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('filament.admin.pages.checksheet-page') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        On PM
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Master Sample Button -->
                    <div
                        class="relative"
                        x-data="{ open: false }"
                    >
                        <x-filament::button
                            color="info"
                            size="sm"
                            class="w-44"
                            @click="open = !open"
                        >
                            <div class="flex items-center justify-center gap-2 text-black">
                                <x-heroicon-s-cog class="h-5 w-5" />
                                Master Sample
                            </div>
                        </x-filament::button>

                        <!-- DROPDOWN MENU KE SAMPING -->
                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="dropdown-right"
                        >
                            <ul class="divide-y divide-gray-200 rounded-lg bg-white shadow-lg">
                                <li>
                                    <a
                                        href="{{ route('filament.admin.pages.checksheet-page') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        Register
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('filament.admin.pages.checksheet-page') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        Storing
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('filament.admin.pages.checksheet-page') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        Running
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="{{ route('filament.admin.pages.checksheet-page') }}"
                                        class="block w-full cursor-pointer px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        On PM
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </x-filament-widgets::widget>
    @endif
</div>
