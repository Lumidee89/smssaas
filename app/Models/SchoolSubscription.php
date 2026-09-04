<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSubscription extends Model
{
    protected $fillable = ['school_id', 'subscription_plan_id', 'status', 'starts_at', 'trial_ends_at', 'current_period_ends_at', 'cancelled_at', 'changed_by'];

    protected $casts = ['starts_at' => 'datetime', 'trial_ends_at' => 'datetime', 'current_period_ends_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
