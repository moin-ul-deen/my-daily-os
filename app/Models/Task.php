<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model {
 use HasFactory; use \Illuminate\Database\Eloquent\SoftDeletes; 
 protected $fillable=['user_id','task_template_id','title','description','category','date','start_time','end_time','priority','status','reminder_minutes','completed_at']; protected $casts=['date'=>'date','completed_at'=>'datetime']; public function user(){ return $this->belongsTo(User::class); } public function template(){ return $this->belongsTo(TaskTemplate::class,'task_template_id'); }
}
