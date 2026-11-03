<?php
namespace App\Http\Controllers;
use App\Models\{NotificationSubscription, Reminder, NotificationLog};
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller {
 public function index(){ $reminders=Reminder::where('user_id',auth()->id())->get(); $logs=NotificationLog::where('user_id',auth()->id())->latest('sent_at')->limit(20)->get(); return view('settings.notifications',compact('reminders','logs')); }

 public function inbox(){
   $items=NotificationLog::where('user_id',auth()->id())->where('channel','in_app')->latest('sent_at')->paginate(30);
   $unread=NotificationLog::where('user_id',auth()->id())->where('channel','in_app')->whereNull('read_at')->count();
   return view('notifications.inbox',compact('items','unread'));
 }

 public function markRead(NotificationLog $log){
   abort_unless($log->user_id===auth()->id(),403);
   if (!$log->read_at) $log->update(['read_at'=>now()]);
   return back();
 }

 public function markAllRead(){
   NotificationLog::where('user_id',auth()->id())->where('channel','in_app')->whereNull('read_at')->update(['read_at'=>now()]);
   return back()->with('success','All notifications marked as read');
 }

 public function sendTest(NotificationService $notify){
   $ref='test-'.now()->timestamp; // unique per click so every test click creates a notification
   NotificationLog::create(['user_id'=>auth()->id(),'type'=>'test','reference_key'=>$ref,'channel'=>'in_app','sent_at'=>now(),'title'=>'Test notification','body'=>'Test notification — notifications are working']);
   $push=$notify->attemptWebPush(auth()->user(),'test',$ref,'Test notification','Test notification — notifications are working');
   $msg=match($push){
     'sent'=>'Test notification sent. Check your Notifications inbox — push was also delivered to your subscribed device.',
     'skipped_no_subscription'=>'Test notification created in your inbox. Push was skipped: this device is not subscribed yet — tap "Enable push on this device" first.',
     'skipped_no_vapid'=>'Test notification created in your inbox. Push was skipped: VAPID keys are not set on the server yet (run php artisan notifications:vapid and paste the keys into .env).',
     default=>'Test notification created in your inbox. Push attempt did not succeed — see storage/logs/laravel.log for details.',
   };
   return back()->with('success',$msg);
 }

 public function storeSubscription(Request $r){
   // Standard Push API shape: {endpoint, keys:{p256dh, auth}} — flat fields also accepted for backward compatibility.
   $endpoint=$r->input('endpoint');
   $p256dh=$r->input('keys.p256dh', $r->input('p256dh', $r->input('public_key')));
   $auth=$r->input('keys.auth', $r->input('auth', $r->input('auth_token')));
   if (!$endpoint) return response()->json(['ok'=>false,'error'=>'endpoint required'],422);
   NotificationSubscription::updateOrCreate(['user_id'=>auth()->id(),'endpoint'=>$endpoint],['public_key'=>$p256dh,'auth_token'=>$auth,'content_encoding'=>$r->input('content_encoding','aes128gcm')]);
   return response()->json(['ok'=>true]);
 }

 public function storeReminder(Request $r){ $v=$r->validate(['type'=>'required|string','title'=>'required|string','time'=>'nullable','minutes_before'=>'nullable|integer','is_enabled'=>'nullable|boolean']); Reminder::create(array_merge($v,['user_id'=>auth()->id()])); return back()->with('success','Reminder saved'); }
 public function destroyReminder(Reminder $reminder){ abort_unless($reminder->user_id===auth()->id(),403); $reminder->delete(); return back(); }
}
