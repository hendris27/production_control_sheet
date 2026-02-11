<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class IronSolderInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'no',
        'shift',
        'actual_setting',
        'esd_voltage',
        'eos_ground',
        'solder_tip_condition',
        'solder_stand_condition',
        'judgement',
        'remarks',
        'line',
        'pic',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->no)) {
                $lastNo = static::max('no');
                $model->no = $lastNo ? $lastNo + 1 : 1;
            }
        });
    }
}
