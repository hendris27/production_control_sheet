<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class OperatosNameList extends Model
{
    use HasFactory;

    protected $table = 'operatos_name_lists';

    protected $fillable = [
        'no',
        'nik',
        'name',
    ];

    protected $casts = [
        'no' => 'integer',
    ];
}

