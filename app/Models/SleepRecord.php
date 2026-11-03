<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SleepRecord extends Model {
 use HasFactory; 
 protected $fillable=['user_id','sleep_start','wake_time','duration_minutes','quality_rating','notes']; protected $casts=['sleep_start'=>'datetime','wake_time'=>'datetime']; public function user(){ return $this->belongsTo(User::class); }
}
