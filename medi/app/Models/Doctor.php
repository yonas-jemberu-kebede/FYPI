<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, 'associated_id', 'id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}
