<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciseLog extends Model {
 use HasFactory; 
 protected $fillable=['user_id','date','exercise_type','duration_minutes','sets','repetitions','weight_kg','notes']; protected $casts=['date'=>'date']; public function user(){ return $this->belongsTo(User::class); }
}
