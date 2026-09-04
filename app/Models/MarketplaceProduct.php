<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MarketplaceProduct extends Model { protected $guarded=[]; protected $casts=['price'=>'decimal:2','is_active'=>'boolean']; public function vendor(){return $this->belongsTo(MarketplaceVendor::class,'marketplace_vendor_id');} }
