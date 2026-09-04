<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WalletTransaction extends Model { protected $guarded=[]; protected $casts=['amount'=>'decimal:2','balance_before'=>'decimal:2','balance_after'=>'decimal:2','metadata'=>'array']; public function wallet(){return $this->belongsTo(Wallet::class);} }
