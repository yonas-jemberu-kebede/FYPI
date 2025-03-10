<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        // 'read_at'=>

    ];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
