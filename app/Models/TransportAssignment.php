<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TransportAssignment extends Model { protected $guarded=[]; public function student(){return $this->belongsTo(Student::class);} public function route(){return $this->belongsTo(TransportRoute::class,'transport_route_id');} public function vehicle(){return $this->belongsTo(TransportVehicle::class,'transport_vehicle_id');} }
