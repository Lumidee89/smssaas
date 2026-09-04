<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HostelAllocation extends Model { protected $guarded=[]; protected $casts=['starts_on'=>'date','ends_on'=>'date']; public function room(){return $this->belongsTo(HostelRoom::class,'hostel_room_id');} public function student(){return $this->belongsTo(Student::class);} }
