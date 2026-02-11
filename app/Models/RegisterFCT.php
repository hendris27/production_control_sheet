<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;

class RegisterFCT extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'register_f_c_t_s';

    protected $fillable = [
        'date_registration',
        'registration_no',
        'customer_by',
        'fabrication_by',
        'product_model',
        'status_fct'
    ];

    protected $attributes = [
        'status_fct' => 'registered'
    ];

    protected $casts = [
        'date_registration' => 'date'
    ];
     public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_by');
    }
}
