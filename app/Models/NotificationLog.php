<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationLog extends Model {
 use HasFactory;
 protected $fillable=['user_id','type','reference_key','channel','sent_at','title','body','read_at'];
 protected $casts=['sent_at'=>'datetime','read_at'=>'datetime'];
 public function user(){ return $this->belongsTo(User::class); }
 public function scopeUnread($q){ return $q->whereNull('read_at'); }
 public function scopeInbox($q){ return $q->where('channel','in_app'); }
}
