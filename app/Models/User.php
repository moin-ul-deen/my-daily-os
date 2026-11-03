<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
 use HasFactory; use Notifiable;
 protected $fillable=['name','email','password','profile_picture','date_of_birth','gender','city','country','timezone','preferred_language'];
protected $hidden=['password','remember_token'];
protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed','date_of_birth'=>'date']; }
public function settings(){ return $this->hasOne(UserSetting::class); }
public function prayerSetting(){ return $this->hasOne(PrayerSetting::class); }
public function tasks(){ return $this->hasMany(Task::class); }
public function prayerRecords(){ return $this->hasMany(PrayerRecord::class); }
public function waterLogs(){ return $this->hasMany(WaterLog::class); }
public function weightLogs(){ return $this->hasMany(WeightLog::class); }
public function meals(){ return $this->hasMany(Meal::class); }
public function sleepRecords(){ return $this->hasMany(SleepRecord::class); }
public function projects(){ return $this->hasMany(Project::class); }
public function habits(){ return $this->hasMany(Habit::class); }
public function financialRecords(){ return $this->hasMany(FinancialRecord::class); }
protected static function booted(){ static::created(function($u){ $u->settings()->create([]); $u->prayerSetting()->create([]); }); }
}
