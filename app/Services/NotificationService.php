<?php
namespace App\Services;
use App\Models\NotificationLog;
use App\Models\NotificationSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class NotificationService {
 /**
  * Send a notification once per (user, type, reference_key, channel).
  * Always writes an in-app inbox record (channel=in_app, with title+body, unread).
  * Then, when the in-app record was created, attempts a real Web Push if the
  * user has subscriptions and VAPID keys are configured. Never throws.
  * Returns true when a new notification was created.
  */
 public function sendOnce($user, string $type, string $referenceKey, string $title, string $body, string $channel = 'in_app'): bool {
   if (NotificationLog::where('user_id', $user->id)->where('type', $type)->where('reference_key', $referenceKey)->where('channel', 'in_app')->exists()) {
     return false;
   }
   NotificationLog::create([
     'user_id' => $user->id, 'type' => $type, 'reference_key' => $referenceKey,
     'channel' => 'in_app', 'sent_at' => now(), 'title' => $title, 'body' => $body,
   ]);
   $this->attemptWebPush($user, $type, $referenceKey, $title, $body);
   return true;
 }

 /**
  * Try to deliver a Web Push for a notification that already has an in-app
  * record. Logs the attempt as a separate webpush channel row. Returns one of:
  * 'sent', 'failed', 'skipped_no_subscription', 'skipped_no_vapid', 'error'.
  * Never throws.
  */
 public function attemptWebPush($user, string $type, string $referenceKey, string $title, string $body): string {
   try {
     $subs = NotificationSubscription::where('user_id', $user->id)->get();
     if ($subs->isEmpty()) return 'skipped_no_subscription';
     $pub = config('services.vapid.public_key'); $priv = config('services.vapid.private_key');
     if (!$pub || !$priv) return 'skipped_no_vapid';
     $ok = $this->sendWebPush($user, $title, $body);
     $status = $ok ? 'sent' : 'failed';
     if (!NotificationLog::where('user_id', $user->id)->where('type', $type)->where('reference_key', $referenceKey)->where('channel', 'webpush')->exists()) {
       NotificationLog::create([
         'user_id' => $user->id, 'type' => $type, 'reference_key' => $referenceKey,
         'channel' => 'webpush', 'sent_at' => now(), 'title' => $title, 'body' => $body,
         'read_at' => now(), // push rows never show as unread inbox items
       ]);
     }
     return $status;
   } catch (\Throwable $e) {
     Log::warning('WebPush attempt failed: '.$e->getMessage());
     return 'error';
   }
 }

 /**
  * Real Web Push delivery via minishlink/web-push.
  * Returns true when at least one subscription accepted the push.
  * Expired subscriptions (404/410) are removed. Never throws.
  */
 public function sendWebPush($user, string $title, string $body): bool {
   try {
     $pub = config('services.vapid.public_key'); $priv = config('services.vapid.private_key');
     $subject = config('services.vapid.subject') ?: 'mailto:admin@example.com';
     if (!$pub || !$priv) { Log::info('WebPush skipped: VAPID keys not configured'); return false; }
     $subs = NotificationSubscription::where('user_id', $user->id)->get();
     if ($subs->isEmpty()) return false;
     $auth = ['VAPID' => ['subject' => $subject, 'publicKey' => $pub, 'privateKey' => $priv]];
     $webPush = new WebPush($auth);
     $payload = json_encode(['title' => $title, 'body' => $body]);
     foreach ($subs as $sub) {
       try {
         $subscription = Subscription::create([
           'endpoint' => $sub->endpoint,
           'publicKey' => $sub->public_key,
           'authToken' => $sub->auth_token,
           'contentEncoding' => $sub->content_encoding ?: 'aes128gcm',
         ]);
         $webPush->queueNotification($subscription, $payload);
       } catch (\Throwable $e) {
         Log::warning('WebPush queue failed for subscription '.$sub->id.': '.$e->getMessage());
       }
     }
     $sent = false;
     foreach ($webPush->flush() as $report) {
       if ($report->isSuccess()) { $sent = true; continue; }
       Log::warning('WebPush delivery failed for '.$report->getEndpoint().': '.$report->getReason());
       if ($report->isSubscriptionExpired()) {
         NotificationSubscription::where('user_id', $user->id)->where('endpoint', $report->getEndpoint())->delete();
       }
     }
     return $sent;
   } catch (\Throwable $e) {
     Log::warning('WebPush failed: '.$e->getMessage());
     return false;
   }
 }
}
