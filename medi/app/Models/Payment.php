<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = ['tx_ref', 'amount', 'currency', 'status', 'type', 'type_id', 'patient_id', 'checkout_url'];

    public function payable()
    {
        return $this->morphTo();
    }
}
