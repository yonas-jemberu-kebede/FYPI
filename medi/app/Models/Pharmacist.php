<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacist extends Model
{
    /** @use HasFactory<\Database\Factories\PharmacistFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, 'associated_id', 'id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    protected $casts = [
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
    ];
}
