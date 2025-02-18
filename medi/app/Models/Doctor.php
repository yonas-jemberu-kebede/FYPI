<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorFactory> */
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'specialization', 'email', 'hospital_id', 'gender', 'phone_number', 'date_of_birth'];

    public function user()
    {
        return $this->hasOne(User::class, 'associated_id', 'id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
