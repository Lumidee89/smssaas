<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Wallet extends Model { protected $guarded=[]; protected $casts=['balance'=>'decimal:2','is_active'=>'boolean']; public function student(){return $this->belongsTo(Student::class);} public function transactions(){return $this->hasMany(WalletTransaction::class);} }
