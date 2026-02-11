<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionControlShift1_5h extends Model
{
    protected $table = 'production_control_shift1_5h';

    protected $guarded = [];

    protected $casts = [
        'slots' => 'array',
    ];
}
