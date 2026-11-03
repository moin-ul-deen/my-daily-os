<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeightLog extends Model {
 use HasFactory; 
 protected $fillable=['user_id','date','weight_kg','note']; protected $casts=['date'=>'date','weight_kg'=>'float']; public function user(){ return $this->belongsTo(User::class); }
}
