// My Daily OS — Web Push subscription (loaded with defer by the layout).
// The "Enable push on this device" button has id="enablePushBtn".
(function () {
  function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
    return outputArray;
  }

  async function enablePush() {
    var btn = document.getElementById('enablePushBtn');
    function note(msg) { if (btn) btn.textContent = msg; alert(msg); }
    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
      return note('Push is not supported in this browser. On iPhone: install the app to your Home Screen first (iOS 16.4+), then open it from there and try again. In-app notifications still work.');
    }
    var vapid = document.querySelector('meta[name="vapid-public-key"]');
    if (!vapid || !vapid.content) {
      return note('Push is not configured on the server yet (no VAPID key). Ask the admin to run: php artisan notifications:vapid and paste the keys into .env. In-app notifications still work.');
    }
    try {
      var permission = await Notification.requestPermission();
      if (permission !== 'granted') return note('Notification permission was not granted. You can allow it from your browser site settings. In-app notifications still work.');
      var reg = await navigator.serviceWorker.ready;
      var sub = await reg.pushManager.getSubscription();
      if (!sub) {
        sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: urlBase64ToUint8Array(vapid.content) });
      }
      var json = sub.toJSON();
      var csrf = document.querySelector('meta[name="csrf-token"]');
      var res = await fetch('/settings/notifications/subscription', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '', 'Accept': 'application/json' },
        body: JSON.stringify({ endpoint: json.endpoint, keys: { p256dh: json.keys.p256dh, auth: json.keys.auth } })
      });
      if (!res.ok) return note('Could not save the push subscription (server error). Please try again.');
      note('Push enabled on this device. Now tap "Send test notification" to check it arrives.');
    } catch (e) {
      console.log(e);
      note('Something went wrong while enabling push: ' + e.message);
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('enablePushBtn');
    if (btn) btn.addEventListener('click', enablePush);
  });
})();
