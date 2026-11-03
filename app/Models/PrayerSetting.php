<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrayerSetting extends Model {
 use HasFactory; 
 protected $fillable=['user_id','city','country','latitude','longitude','timezone','calculation_method','fajr_angle','isha_angle','asr_juristic','asr_shadow_factor','high_latitude_rule','adjustments','reminder_settings']; protected $casts=['adjustments'=>'array','reminder_settings'=>'array','latitude'=>'float','longitude'=>'float']; public function user(){ return $this->belongsTo(User::class); }
}
