<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestPrice extends Model
{
    /** @use HasFactory<\Database\Factories\TestPriceFactory> */
    use HasFactory;

    protected $guarded = [];

    public function diagnosticCenters()
    {
        return $this->belongsTo(DiagnosticCenter::class);
    }
}
