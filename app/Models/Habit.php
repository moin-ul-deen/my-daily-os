<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Habit extends Model {
 use HasFactory; use \Illuminate\Database\Eloquent\SoftDeletes; 
 protected $fillable=['user_id','name','description','category','frequency','target','start_date','end_date','is_active']; protected $casts=['start_date'=>'date','end_date'=>'date','is_active'=>'boolean']; public function user(){ return $this->belongsTo(User::class); } public function logs(){ return $this->hasMany(HabitLog::class); }
}
