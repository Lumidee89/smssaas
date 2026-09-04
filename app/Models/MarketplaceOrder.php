<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MarketplaceOrder extends Model { protected $guarded=[]; protected $casts=['total'=>'decimal:2']; public function student(){return $this->belongsTo(Student::class);} public function items(){return $this->hasMany(MarketplaceOrderItem::class);} }
