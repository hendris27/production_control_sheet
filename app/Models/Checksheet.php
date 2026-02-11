<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checksheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', // daily, monthly, 3monthly
        'category', // inspection_line, machine_solder
        'name',
    ];
}
