<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = ['school_id', 'school_subscription_id', 'reference', 'amount', 'currency', 'status', 'provider', 'provider_reference', 'paid_at', 'metadata'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'metadata' => 'array'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function subscription()
    {
        return $this->belongsTo(SchoolSubscription::class, 'school_subscription_id');
    }
}
