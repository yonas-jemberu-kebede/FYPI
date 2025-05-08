<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingBooking extends Model
{
    protected $guarded = [];

    protected $casts = ['data' => 'array'];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
