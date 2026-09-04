<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LibraryItem extends Model { protected $guarded=[]; public function loans(){return $this->hasMany(LibraryLoan::class);} }
