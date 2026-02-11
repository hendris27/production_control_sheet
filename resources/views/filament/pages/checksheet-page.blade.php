<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">

        {{-- Card pertama: Inspection Line --}}
        <x-filament::link href="{{ route('filament.admin.resources.checksheet-lists.index') }}"
            class="bg-blue-500 text-white rounded-lg shadow p-6 hover:bg-blue-600 transition flex flex-col items-center justify-center">
            <h2 class="text-lg font-semibold">Inspection Line</h2>
        </x-filament::link>

        {{-- Card kedua: Machine Solder --}}
        <div class="bg-gray-500 text-black rounded-lg shadow p-6 hover:bg-blue-600 transition">
            <h2 class="text-lg font-semibold mb-4 text-center">Machine Solder</h2>

            <select id="machine_solder"
                class="w-full p-3 rounded-lg text-black border-gray-300 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                onchange="if(this.value) window.location.href=this.value">
                <option value="">-- Pilih Jenis Solder --</option>
                <option class="text-black"
                    value="{{ route('filament.admin.resources.iron-solder-inspections.index') }}?type=manual">
                    Manual Solder
                </option>
                <option class="text-black"
                    value="{{ route('filament.admin.resources.checksheet-lists.index') }}?type=otomatis">
                    Otomatis Solder
                </option>
            </select>
        </div>
    </div>
</x-filament-panels::page>
