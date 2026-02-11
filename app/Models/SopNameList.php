<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopNameList extends Model
{
    use HasFactory;

    protected $table = 'sop_name_lists';

    protected $fillable = [
        'no',
        'name',
        'nik',
    ];

    protected $casts = [
        'no' => 'integer',
    ];
}
