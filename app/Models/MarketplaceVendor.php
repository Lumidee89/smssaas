<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MarketplaceVendor extends Model { protected $guarded=[]; public function products(){return $this->hasMany(MarketplaceProduct::class);} }
