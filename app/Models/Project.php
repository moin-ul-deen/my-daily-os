<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model {
 use HasFactory; use \Illuminate\Database\Eloquent\SoftDeletes; 
 protected $fillable=['user_id','name','client_name','platform','description','start_date','deadline','status','priority','estimated_value','actual_revenue','currency','notes']; protected $casts=['start_date'=>'date','deadline'=>'date']; public function user(){ return $this->belongsTo(User::class); } public function incomes(){ return $this->hasMany(FreelanceIncome::class); } public function tasks(){ return $this->hasMany(ProjectTask::class); }
}
