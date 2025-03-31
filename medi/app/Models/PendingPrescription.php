<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingPrescription extends Model
{
    /** @use HasFactory<\Database\Factories\PendingPrescriptionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'medications' => 'array',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function pharmacist()
    {
        return $this->belongsTo(Pharmacist::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
