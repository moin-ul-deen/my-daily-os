const CACHE='my-daily-os-v1'; const STATIC=['/css/app.css','/js/app.js','/icons/icon.svg','/offline.html','/manifest.json'];
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(STATIC))); self.skipWaiting();});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(ks=>Promise.all(ks.filter(k=>k!==CACHE).map(k=>caches.delete(k))))); self.clients.claim();});
self.addEventListener('fetch',e=>{ if(e.request.method!=='GET')return; const url=new URL(e.request.url);
 if(STATIC.includes(url.pathname)){ e.respondWith(caches.match(e.request).then(r=>r||fetch(e.request))); return; }
 e.respondWith(fetch(e.request).then(res=>{ const copy=res.clone(); caches.open(CACHE).then(c=>c.put(e.request,copy)); return res; }).catch(()=>caches.match(e.request).then(r=>r||caches.match('/offline.html'))));
});
self.addEventListener('push',e=>{ let d={title:'My Daily OS',body:'You have a reminder'}; try{d=e.data.json();}catch(_){ if(e.data) d.body=e.data.text(); } e.waitUntil(self.registration.showNotification(d.title,{body:d.body,icon:'/icons/icon.svg'})); });
self.addEventListener('notificationclick',e=>{ e.notification.close(); e.waitUntil(clients.openWindow('/dashboard')); });
