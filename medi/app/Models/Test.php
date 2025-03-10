<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    /** @use HasFactory<\Database\Factories\TestFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'lab_technician_id',
        'amount',
        'status',
        'test_requests',
        'test_results',
        'test_date'
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


    public function labTechnician()
    {
        return $this->belongsTo(LabTechnician::class);
    }
public function testPrices(){
    return $this->hasMany(TestPrice::class);
}
    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
