<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $fillable = ['patient_id', 'doctor_id', 'hospital_id', 'appointment_date', 'appointment_time', 'amount', 'status', 'video_chat_link', 'video_chat_link_date'];

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

    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
