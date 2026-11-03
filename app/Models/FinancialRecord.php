<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinancialRecord extends Model {
 use HasFactory; use \Illuminate\Database\Eloquent\SoftDeletes; 
 protected $fillable=['user_id','type','category','amount','currency','date','payment_method','description','attachment','is_recurring','recurrence']; protected $casts=['date'=>'date','amount'=>'decimal:2','is_recurring'=>'boolean']; public function user(){ return $this->belongsTo(User::class); }
}
