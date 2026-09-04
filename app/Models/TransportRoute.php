<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TransportRoute extends Model { protected $guarded=[]; protected $casts=['stops'=>'array','term_fee'=>'decimal:2','is_active'=>'boolean']; }
