@php
    /** @var \\Illuminate\Support\Collection $customerPcb */
@endphp

<div class="filament-widgets-widget">
    <div class="grid grid-cols-1 gap-4">
        <div class="rounded bg-white p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Total Customers</div>
                    <div class="text-2xl font-semibold">{{ $totalCustomers }}</div>
                </div>
            </div>
        </div>

        <div class="rounded bg-white p-4 shadow">
            <div class="mb-2 text-sm font-medium text-gray-700">PCB Output by Customer (top 20)</div>
            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500">
                            <th class="py-1">#</th>
                            <th class="py-1">Customer</th>
                            <th class="py-1">Total PCB</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($customerPcb) && $customerPcb && count($customerPcb) > 0)
                            @foreach ($customerPcb as $i => $row)
                                <tr class="border-t">
                                    <td class="py-2">{{ $i + 1 }}</td>
                                    <td class="py-2">{{ $row->customer_name ?? ($row->customer_id ?? '-') }}</td>
                                    <td class="py-2">{{ number_format($row->total_output) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td
                                    class="py-2"
                                    colspan="3"
                                >No production data yet.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
