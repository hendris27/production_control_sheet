<?php

namespace App\Filament\Resources\ProductionControlShift1Resource\Pages;

use App\Filament\Resources\ProductionControlShift1Resource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionControlShift1 extends CreateRecord
{
    protected static string $resource = ProductionControlShift1Resource::class;
    protected static ?string $title = 'Form Production Control Sheet';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // set a flag so observer knows this create comes from Filament UI
        app()->instance('creating_by_filament', true);

        // Ambil work_hours dan shift untuk menentukan timeslot mana yang digunakan
        $workHours = $data['work_hours'] ?? '7 Hours';
        $shift = $data['select_shift'] ?? ($data['shift'] ?? '1');
        $timeSlotClass = $workHours === '5 Hours'
            ? '\\App\\Models\\TimeSlotShift' . $shift . '_5h'
            : '\\App\\Models\\TimeSlotShift' . $shift . '_7h';

        if (class_exists($timeSlotClass)) {
            $timeSlots = $timeSlotClass::orderBy('order')->get();

            // Siapkan array untuk slots
            $slots = [];

            // Try to get target per hour model data if available (fallback)
            $modelForUph = $data['model'] ?? null;
            $targetModelForUph = null;
            if (! empty($modelForUph)) {
                $targetModelForUph = \App\Models\TargetUph::whereRaw('LOWER(model_name)=?', [strtolower($modelForUph)])->first();
            }

            foreach ($timeSlots as $ts) {
                $slug = $ts->slug;

                // Determine target value: if the target field is present in submitted data,
                // use it (even if empty/0). Only when the field is absent, compute fallback.
                $computedTarget = 0;
                if ($targetModelForUph) {
                    $minutes = $ts->minutes ?? $ts->duration ?? 60;
                    $computedTarget = (int) round(($targetModelForUph->target_per_hour / 60) * $minutes);
                }

                // Prefer the raw HTTP request if present (handles empty-string submission),
                // otherwise fall back to $data array and finally computed target.
                $requestAll = request()->all();
                if (array_key_exists('target_' . $slug, $requestAll)) {
                    $finalTarget = (int) ($requestAll['target_' . $slug] === '' ? 0 : $requestAll['target_' . $slug]);
                } elseif (array_key_exists('target_' . $slug, $data)) {
                    $finalTarget = (int) ($data['target_' . $slug] === '' ? 0 : $data['target_' . $slug]);
                } else {
                    $finalTarget = $computedTarget;
                }

                // Collect per-slot data from form fields
                $actualVal = (int) ($data['actual_ok_' . $slug] ?? 0);
                $ngVal = (int) ($data['ng_' . $slug] ?? 0);
                $lossVal = (int) ($data['loss_time_' . $slug] ?? 0);
                // Jika actual = 0, simpan target dan balance sebagai 0
                $targetToSave = ($actualVal > 0) ? $finalTarget : 0;
                $balanceToSave = ($actualVal > 0) ? ($actualVal - $targetToSave) : 0;

                $slots[$slug] = [
                    'slug' => $slug,
                    'label' => $ts->label,
                    'target' => $targetToSave,
                    'actual_ok' => $actualVal,
                    'ng' => $ngVal,
                    'balance' => $balanceToSave,
                    'loss_time' => $lossVal,
                    'remarks' => $data['remarks_' . $slug] ?? null,
                    'remarks_other' => $data['remarks_other_' . $slug] ?? null,
                    'target_acc' => (int) ($data['target_acc_' . $slug] ?? 0),
                    'actual_acc' => (int) ($data['actual_acc_' . $slug] ?? 0),
                    'ng_acc' => (int) ($data['ng_acc_' . $slug] ?? 0),
                    'loss_acc' => (int) ($data['loss_acc_' . $slug] ?? 0),
                    'balance_acc' => (int) ($data['balance_acc_' . $slug] ?? 0),
                ];

                // include technician/name_techinician per slot if present
                $slots[$slug]['technician'] = $data['name_techinician' . $slug] ?? null;

                // Hapus field individual dari data agar tidak tersimpan sebagai column
                unset(
                    $data['target_' . $slug],
                    $data['actual_ok_' . $slug],
                    $data['ng_' . $slug],
                    $data['balance_' . $slug],
                    $data['loss_time_' . $slug],
                    $data['remarks_' . $slug],
                    $data['remarks_other_' . $slug],
                    $data['target_acc_' . $slug],
                    $data['actual_acc_' . $slug],
                    $data['ng_acc_' . $slug],
                    $data['loss_acc_' . $slug],
                    $data['balance_acc_' . $slug]
                    , $data['name_techinician' . $slug]
                );
            }

            // Simpan slots sebagai JSON
            $data['slots'] = $slots;
            // Hitung totals dari slots supaya pasti tersimpan
            $targetTotal = 0;
            $actualTotal = 0;
            $ngTotal = 0;
            $lossTotal = 0;
            foreach ($slots as $s) {
                $actual = (int) ($s['actual_ok'] ?? 0);
                if ($actual > 0) {
                    $targetTotal += (int) ($s['target'] ?? 0);
                }
                $actualTotal += $actual;
                $ngTotal += (int) ($s['ng'] ?? 0);
                $lossTotal += (int) ($s['loss_time'] ?? 0);
            }
            $data['target_total'] = $targetTotal;
            $data['actual_total'] = $actualTotal;
            $data['ng_total'] = $ngTotal;
            $data['loss_total'] = $lossTotal;
            $data['balance_total'] = ($actualTotal - $targetTotal);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        try {
            $id = $this->record->id ?? null;
            \Illuminate\Support\Facades\Log::info('CreateProductionControlShift1: afterCreate invoked', ['id' => $id, 'creating_by_filament' => app()->bound('creating_by_filament')]);
            if ($id) {
                \Illuminate\Support\Facades\Log::info('CreateProductionControlShift1: attempting dispatch', ['id' => $id]);
                try {
                    \Illuminate\Support\Facades\Bus::dispatchSync(new \App\Jobs\GenerateProductionReports([$id]));
                    \Illuminate\Support\Facades\Log::info('CreateProductionControlShift1: dispatchSync succeeded', ['id' => $id]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('CreateProductionControlShift1: dispatchSync failed, attempting direct handle fallback', ['id' => $id, 'error' => $e->getMessage()]);
                    try {
                        (new \App\Jobs\GenerateProductionReports([$id]))->handle();
                        \Illuminate\Support\Facades\Log::info('CreateProductionControlShift1: fallback direct handle succeeded', ['id' => $id]);
                    } catch (\Throwable $e2) {
                        \Illuminate\Support\Facades\Log::error('CreateProductionControlShift1: fallback direct handle failed', ['id' => $id, 'error' => $e2->getMessage()]);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('CreateProductionControlShift1: failed during afterCreate flow: ' . $e->getMessage());
        }

        // cleanup the temporary flag
        if (app()->bound('creating_by_filament')) {
            app()->forgetInstance('creating_by_filament');
        }
    }
}
