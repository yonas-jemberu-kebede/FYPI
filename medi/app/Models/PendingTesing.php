<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingTesing extends Model
{
    /** @use HasFactory<\Database\Factories\PendingTesingFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'test_requests' => 'array',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function labTechnician()
    {
        return $this->belongsTo(LabTechnician::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
