<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacist extends Model
{
    /** @use HasFactory<\Database\Factories\PharmacistFactory> */
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'email', 'pharmacy_id', 'gender', 'phone_number', 'date_of_birth'];

    public function user()
    {
        return $this->hasOne(User::class, 'associated_id', 'id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
