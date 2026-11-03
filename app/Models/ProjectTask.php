<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectTask extends Model {
 use HasFactory; 
 protected $fillable=['project_id','user_id','title','date','status','duration_minutes']; protected $casts=['date'=>'date']; public function project(){ return $this->belongsTo(Project::class); }
}
