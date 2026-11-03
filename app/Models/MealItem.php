<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MealItem extends Model {
 use HasFactory; 
 protected $fillable=['meal_id','user_id','food_name','quantity','calories','protein_g','carbs_g','fats_g']; public function meal(){ return $this->belongsTo(Meal::class); } public function user(){ return $this->belongsTo(User::class); }
}
