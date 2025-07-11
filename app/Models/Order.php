<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    protected $casts = [
        'versicherter' => 'array',
        'address' => 'array',
        'antragsteller' => 'array',
        'products' => 'array',
        'dispatch_months' => 'array',
        'last_dispatch' => 'date',
        'geburtsdatum' => 'date:d.m.Y',
        'reuseable_bed_protection' => 'boolean',
        'changeProvider' => 'boolean',
        'requestBedPads' => 'boolean',
        'isSameAsContact' => 'boolean',
        'consultation_check' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
