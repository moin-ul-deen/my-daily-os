<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meal extends Model {
 use HasFactory; 
 protected $fillable=['user_id','meal_type','date','time','is_home_cooked','notes']; protected $casts=['date'=>'date','is_home_cooked'=>'boolean']; public function user(){ return $this->belongsTo(User::class); } public function items(){ return $this->hasMany(MealItem::class); }
}
