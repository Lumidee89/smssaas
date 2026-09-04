<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HostelRoom extends Model { protected $guarded=[]; protected $casts=['fee'=>'decimal:2']; public function building(){return $this->belongsTo(HostelBuilding::class,'hostel_building_id');} public function allocations(){return $this->hasMany(HostelAllocation::class);} }
