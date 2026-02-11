<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpvNameList extends Model
{
    use HasFactory;

    protected $table = 'spv_name_lists';

    protected $fillable = [
        'no',
        'nik',
        'name',
    ];
    
    protected $casts = [
        'no' => 'integer',
    ];
}
