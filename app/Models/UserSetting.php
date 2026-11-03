<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSetting extends Model {
 use HasFactory; 
 protected $fillable=['user_id','currency','weight_unit','distance_unit','time_format','date_format','theme','accent_color','target_weight','water_target_ml','walking_target_km','weekly_exercise_sessions','office_start','office_end','working_days','monthly_salary','notifications_enabled','onboarding_dismissed','quiet_start','quiet_end']; protected $casts=['working_days'=>'array','notifications_enabled'=>'boolean','onboarding_dismissed'=>'boolean','monthly_salary'=>'decimal:2']; public function user(){ return $this->belongsTo(User::class); }
}
