<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    protected $casts = [
        'products' => 'array',
        'dispatch_months' => 'array',
        'reuseable_bed_protection' => 'boolean',
        'last_dispatch'                => 'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
