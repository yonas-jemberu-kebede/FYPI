<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['email', 'password', 'role', 'associated_id', 'first_name', 'last_name'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    public function associatedEntity()
    {
        return match ($this->role) {
            'Patient' => $this->belongsTo(Patient::class, 'associated_id', 'id'),
            'Doctor' => $this->belongsTo(Doctor::class, 'associated_id', 'id'),
            'Pharmacist' => $this->belongsTo(Pharmacist::class, 'associated_id', 'id'),
            'Lab Technician' => $this->belongsTo(LabTechnician::class, 'associated_id', 'id'),
            'Hospital Admin' => $this->belongsTo(Hospital::class, 'associated_id', 'id'),
            'Super Admin' => null, // Super Admin is system-wide
            default => null,
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function patients(){
        return $this->hasMany(patient::class);
    }
}
