<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionControlShift1_7h extends Model
{
    protected $table = 'production_control_shift1_7h';

    protected $guarded = [];

    protected $casts = [
        'slots' => 'array',
    ];
}
