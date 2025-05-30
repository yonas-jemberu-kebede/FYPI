<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationInventory extends Model
{
    /** @use HasFactory<\Database\Factories\MedicationInventoryFactory> */
    use HasFactory;

    protected $guarded = [];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
