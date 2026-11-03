<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeRecord extends Model {
 use HasFactory; 
 protected $fillable=['user_id','date','work_start','work_end','break_minutes','total_hours','is_late','is_leave','leave_type','notes']; protected $casts=['date'=>'date','work_start'=>'datetime','work_end'=>'datetime','is_late'=>'boolean','is_leave'=>'boolean']; public function user(){ return $this->belongsTo(User::class); }
}
