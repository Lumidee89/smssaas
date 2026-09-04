<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LibraryLoan extends Model { protected $guarded=[]; protected $casts=['borrowed_at'=>'datetime','due_on'=>'date','returned_at'=>'datetime','fine_amount'=>'decimal:2']; public function item(){return $this->belongsTo(LibraryItem::class,'library_item_id');} public function student(){return $this->belongsTo(Student::class);} }
