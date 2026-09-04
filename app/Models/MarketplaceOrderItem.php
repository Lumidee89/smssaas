<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MarketplaceOrderItem extends Model { protected $guarded=[]; protected $casts=['unit_price'=>'decimal:2','total'=>'decimal:2']; public function product(){return $this->belongsTo(MarketplaceProduct::class,'marketplace_product_id');} }
