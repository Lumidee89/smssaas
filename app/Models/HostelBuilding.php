<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HostelBuilding extends Model { protected $guarded=[]; public function rooms(){return $this->hasMany(HostelRoom::class);} }
