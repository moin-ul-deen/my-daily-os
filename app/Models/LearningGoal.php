<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LearningGoal extends Model {
 use HasFactory; use \Illuminate\Database\Eloquent\SoftDeletes; 
 protected $fillable=['user_id','title','description','skill','type','target_date','progress_percentage','status','resource_link']; protected $casts=['target_date'=>'date']; public function user(){ return $this->belongsTo(User::class); } public function sessions(){ return $this->hasMany(LearningSession::class); }
}
