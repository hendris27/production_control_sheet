<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Customer;
use App\Models\ProductionControlShift1;

class CustomerPcbWidget extends Widget
{
    protected static string $view = 'filament.widgets.customer-pcb-widget';

    public int $totalCustomers = 0;
    public $customerPcb;
    protected function getData(): array
    {
        return [
            'totalCustomers' => $this->totalCustomers,
            'customerPcb' => $this->customerPcb,
        ];
    }

    public function mount(): void
    {
        $this->totalCustomers = Customer::count();

        $this->customerPcb = ProductionControlShift1::selectRaw('customer_id, COALESCE(customer_name, "-") as customer_name, COALESCE(SUM(output), 0) as total_output')
            ->groupBy('customer_id', 'customer_name')
            ->orderByDesc('total_output')
            ->limit(20)
            ->get();
    }
}
