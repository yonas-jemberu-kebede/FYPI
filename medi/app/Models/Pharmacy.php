<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    /** @use HasFactory<\Database\Factories\PharmacyFactory> */
    use HasFactory;

    protected $fillable = ['name', 'address', 'hospital_id', 'email', 'phone_number'];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function pharmacists()
    {
        return $this->hasMany(Pharmacist::class);
    }
}
