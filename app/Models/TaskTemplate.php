<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskTemplate extends Model {
 use HasFactory; use \Illuminate\Database\Eloquent\SoftDeletes; 
 protected $fillable=['user_id','title','description','category','start_time','end_time','priority','recurrence_type','recurrence_days','recurrence_interval','starts_on','ends_on','is_active','reminder_minutes']; protected $casts=['recurrence_days'=>'array','starts_on'=>'date','ends_on'=>'date','is_active'=>'boolean']; public function user(){ return $this->belongsTo(User::class); } public function tasks(){ return $this->hasMany(Task::class); }
}
