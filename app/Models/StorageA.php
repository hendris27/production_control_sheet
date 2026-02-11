<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageA extends Model
{
    protected $fillable = [
        'no',
        'category',
        'model_name',
        'customer',
        'status',
        'location',
        'remark',
        'shift',
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

