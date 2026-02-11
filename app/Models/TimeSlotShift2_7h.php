<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlotShift2_7h extends Model
{
    protected $fillable = ['label','start_time','end_time','minutes','slug','order'];

    // Normalisasi: selalu sediakan atribut minutes (fallback ke duration atau 60)
    public function getMinutesAttribute()
    {
        return $this->attributes['minutes'] ?? $this->attributes['duration'] ?? 60;
    }
}
