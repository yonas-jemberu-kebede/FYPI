<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingBooking extends Model
{
    protected $fillable = ['tx_ref', 'data', 'payment_id', 'hospital_id'];

    protected $casts = ['data' => 'array'];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
