<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrayerTime extends Model {
 use HasFactory; 
 protected $fillable=['user_id','date','fajr','sunrise','dhuhr','asr','maghrib','isha','calculation_method','source']; protected $casts=['date'=>'date']; public function user(){ return $this->belongsTo(User::class); }
}
