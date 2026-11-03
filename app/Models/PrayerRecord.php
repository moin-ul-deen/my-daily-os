<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrayerRecord extends Model {
 use HasFactory; 
 public const STATUSES=['waqt_par','late','qaza','missed']; protected $fillable=['user_id','prayer_name','prayer_date','scheduled_time','actual_time','status','note']; protected $casts=['prayer_date'=>'date']; public function user(){ return $this->belongsTo(User::class); } public function scopeForDate($q,$d){ return $q->whereDate('prayer_date',$d); }
}
