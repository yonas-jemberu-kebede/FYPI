<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTechnician extends Model
{
    /** @use HasFactory<\Database\Factories\LabTechnicianFactory> */
    use HasFactory;

    protected $fillable = [];

    public function user()
    {
        return $this->hasOne(User::class, 'associated_id', 'id');
    }

    public function diagnosticCenter()
    {
        return $this->belongsTo(DiagnosticCenter::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    protected $casts = [
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
    ];
}
