<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class LabTechnician extends Model
{
    /** @use HasFactory<\Database\Factories\LabTechnicianFactory> */
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'email', 'diagnostic_center_id', 'gender', 'phone_number', 'date_of_birth'];

    public function user()
    {
        return $this->hasOne(User::class, 'associated_id', 'id');
    }

    public function diagnosticCenter()
    {
        return $this->belongsTo(DiagnosticCenter::class);
    }
    public function notifications(){
        return $this->morphMany(Notification::class,'notifiable');
    }
}
