<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    protected $casts = [
        'products' => 'array',
        'dispatch_months' => 'array',
        'reusable_bed_protection' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
