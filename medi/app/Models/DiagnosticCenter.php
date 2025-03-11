<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticCenter extends Model
{
    /** @use HasFactory<\Database\Factories\DiagnosticCenterFactory> */
    use HasFactory;

    protected $fillable = ['name', 'hospital_id', 'email', 'phone_number'];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function labTechnicians()
    {
        return $this->hasMany(LabTechnician::class);
    }

    public function testPrices()
    {
        return $this->hasMany(TestPrice::class);
    }
}
