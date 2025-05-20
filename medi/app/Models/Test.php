<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    /** @use HasFactory<\Database\Factories\TestFactory> */
    use HasFactory;

    protected $guarded = [
    ];

    protected $casts = [
        'test_requests' => 'array',
        'test_results' => 'array',
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
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function diagnosticCenter()
    {
        return $this->belongsTo(DiagnosticCenter::class);
    }

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
