<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskCompletion extends Model {
 use HasFactory; 
 protected $fillable=['user_id','task_id','completed_at','note']; protected $casts=['completed_at'=>'datetime']; public function user(){ return $this->belongsTo(User::class); } public function task(){ return $this->belongsTo(Task::class); }
}
