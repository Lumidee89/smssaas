<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['code', 'name', 'monthly_price', 'currency', 'max_students', 'max_staff', 'features', 'is_active', 'sort_order'];

    protected $casts = ['monthly_price' => 'decimal:2', 'features' => 'array', 'is_active' => 'boolean'];
}
