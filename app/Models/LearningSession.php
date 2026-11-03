<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LearningSession extends Model {
 use HasFactory; 
 protected $fillable=['user_id','learning_goal_id','topic','date','duration_minutes','skill','notes','resource_link']; protected $casts=['date'=>'date']; public function user(){ return $this->belongsTo(User::class); } public function goal(){ return $this->belongsTo(LearningGoal::class,'learning_goal_id'); }
}
