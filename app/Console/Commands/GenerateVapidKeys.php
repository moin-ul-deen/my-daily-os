<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command {
 protected $signature = 'notifications:vapid';
 protected $description = 'Generate a VAPID keypair for Web Push notifications';

 public function handle(): int {
   try {
     $keys = VAPID::createVapidKeys();
   } catch (\Throwable $e) {
     $this->error('Could not generate VAPID keys: '.$e->getMessage());
     $this->line('This is usually an OpenSSL config issue on the server. Try one of:');
     $this->line('1. Set OPENSSL_CONF to your openssl.cnf path and re-run, e.g. on Linux: OPENSSL_CONF=/etc/ssl/openssl.cnf php artisan notifications:vapid');
     $this->line('2. Or generate keys with Node: npx web-push generate-vapid-keys and paste the two keys into .env as VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY.');
     return self::FAILURE;
   }
   $this->info('Your new VAPID keys (keep the private key secret):');
   $this->newLine();
   $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
   $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
   $this->line('VAPID_SUBJECT=mailto:you@yourdomain.com');
   $this->newLine();
   $this->info('Next steps:');
   $this->line('1. Paste the three lines above into your .env file (replace any empty VAPID_* lines).');
   $this->line('2. Run: php artisan config:clear');
   $this->line('3. Open Settings → Notifications in the app and tap "Enable push on this device", then "Send test notification".');
   return self::SUCCESS;
 }
}
