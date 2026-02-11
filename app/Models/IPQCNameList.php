<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPQCNameList extends Model
{
    use HasFactory;

    protected $table = 'i_p_q_c_name_lists';
    protected $fillable = [
        'no',
        'name',
        'nik',
    ];

    protected $casts = [
        'no' => 'integer',
    ];
}
