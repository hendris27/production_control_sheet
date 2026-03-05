<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionControlDraft extends Model
{
    use HasFactory;

    protected $table = 'production_control_drafts';

    protected $fillable = [
        'user_id',
        'resource',
        'record_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
