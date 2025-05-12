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
        'reuseable_bed_protection' => 'boolean',
        'changeProvider' => 'boolean',
        'last_dispatch' => 'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
