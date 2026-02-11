<x-filament-panels::page>
    <h1 class="text-2xl font-bold mb-6">Grid Checksheet Line</h1>

    {{-- Search --}}
    <div class="mb-4">
        <input type="text" wire:model.debounce.300ms="search" placeholder="Search..."
            class="w-full md:w-1/3 p-2 border rounded shadow-sm">
    </div>

    {{-- Grid Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @forelse ($records as $record)
            <a href="{{ route('filament.admin.resources.checksheet-lists.edit', $record) }}"
                class="bg-blue-500 text-black font-semibold rounded-lg shadow p-6 text-center hover:bg-blue-600 transition">
                {{ $record->name }}
            </a>
        @empty
            <div class="col-span-full text-center text-gray-500 py-10">
                Tidak ada data
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $records->links() }}
    </div>
</x-filament-panels::page>
