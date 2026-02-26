<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionControlShift1 extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'model',
        'dj_number',
        'customer_id',
        'customer_name',
        'line',
        'select_shiftgroup',
        // new separate fields for shift/group selection
        'select_shift',
        'select_group',
        // persisted values (set by the form's afterStateUpdated)
        'shift',
        'group',
        'shift_group',
        'work_hours',
        'slots',
        'operators',
        'quality_information',
        'model_output',
        'start_time',
        'end_time',
        'output',
        'start_time_add',
        'end_time_add',
        'output_add',
        'output_total_ok',
        'remark_output',
        'target_total',
        'actual_total',
        'total_qty',
        'qty_ok',
        'qty_ng',
        'ng_total',
        'loss_total',
        'balance_total',
        'issued_sop',
        'checked_leader',
        'approved_spv',
        'downloaded_at',
    ];

    protected $casts = [
        'date' => 'date',
        // 'time' is not a valid built-in cast in Laravel; use string for time-only fields
        'start_time' => 'string',
        'end_time' => 'string',
        'slots' => 'array',
        'operators' => 'array',
        'quality_information' => 'array',
        'qty' => 'integer',
        'output' => 'integer',
        'target_total' => 'integer',
        'actual_total' => 'integer',
        'total_qty' => 'integer',
        'qty_ok' => 'integer',
        'qty_ng' => 'integer',
        'ng_total' => 'integer',
        'loss_total' => 'integer',
        'balance_total' => 'integer',
        // new simple casts
        'select_shift' => 'string',
        'select_group' => 'string',
        'shift' => 'string',
        'group' => 'string',
        'downloaded_at' => 'datetime',
    ];

    public static function processList(): array
    {
        $list = [
            'FV',
            'FCT',
            'MI',
            'PRESS',
            'VMI',
            'WELDING',
            'ASSY',
            'HEATING',
            'COATING',
            'GREASE',
            'LED TEST',
            'SELECTIVE',
            'ROOMWRITE',
            'KEYENCE',
            'INSERT',
            'LEVELING',
            'FLUXER',
            'TAPING',
            'SCREW',
            'ROBOTIC',
            'CAULKING',
            'JUMPER 1',
            'JUMPER 2',
            'TU',
            'TU1',
            'TU2',
            'TAKE OUT PALLET',
        ];

        // Return associative array so Filament stores the process name as value.
        return array_combine($list, $list);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
