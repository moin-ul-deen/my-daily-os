<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalkingLog extends Model {
 use HasFactory; 
 protected $fillable=['user_id','date','distance_km','duration_minutes','steps','calories','notes']; protected $casts=['date'=>'date']; public function user(){ return $this->belongsTo(User::class); }
}
