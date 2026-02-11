<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetUph extends Model
{
    protected $table = 'target_uphs';

    protected $fillable = [
        'model_name',
        'target_per_hour',
    ];
}
