<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    /** @use HasFactory<\Database\Factories\HospitalFactory> */
    use HasFactory;

    protected $fillable = ['name', 'email', 'address', 'phone_number', 'gender', 'account'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function pharmacy()
    {
        return $this->hasOne(Pharmacy::class);
    }

    public function diagnosticCenter()
    {
        return $this->hasOne(DiagnosticCenter::class);
    }

    public function admin()
    {
        return $this->hasOne(User::class, 'associated_id', 'id')->where('role', 'Hospital Admin');
    }
    public function notifications(){
        return $this->morphMany(Notification::class,'notifiable');
    }
}
