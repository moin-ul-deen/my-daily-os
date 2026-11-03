<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryRecord extends Model {
 use HasFactory; 
 protected $fillable=['user_id','month','amount','currency','overtime_amount','notes']; protected $casts=['month'=>'date']; public function user(){ return $this->belongsTo(User::class); }
}
