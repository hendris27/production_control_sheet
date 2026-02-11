<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianNameList extends Model
{
    use HasFactory;

    protected $table = 'technician_name_lists';

    protected $fillable = [
        'no',
        'nik',
        'name',
    ];
}
