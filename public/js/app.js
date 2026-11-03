if('serviceWorker' in navigator){window.addEventListener('load',()=>navigator.serviceWorker.register('/sw.js').catch(()=>{}));}
document.addEventListener('DOMContentLoaded',()=>{
  const el=document.getElementById('clock');
  if(el){const tick=()=>{el.textContent=new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});};tick();setInterval(tick,1000);}

  /* Two-step delete confirmation (app-wide):
     First click arms the button ("Confirm delete?"), second click submits.
     Applies to any submit button with .btn-delete-confirm inside a DELETE form. */
  document.querySelectorAll('form').forEach(function(form){
    const isDelete = form.querySelector('input[name="_method"][value="DELETE"]');
    if(!isDelete) return;
    form.querySelectorAll('button[type="submit"],button:not([type])').forEach(function(btn){
      if(!/delete/i.test(btn.textContent) && !btn.classList.contains('btn-outline-danger')) return;
      btn.classList.add('btn-delete-confirm');
      btn.removeAttribute('onclick');
    });
  });
  document.addEventListener('click',function(e){
    const btn=e.target.closest('.btn-delete-confirm');
    if(!btn || btn.dataset.armed==='1') return;
    e.preventDefault();
    const original=btn.textContent;
    btn.dataset.armed='1'; btn.dataset.original=original;
    btn.textContent='Confirm delete?'; btn.classList.add('btn-confirm-delete');
    setTimeout(()=>{ if(btn.dataset.armed==='1'){ btn.dataset.armed=''; btn.textContent=btn.dataset.original||'Delete'; btn.classList.remove('btn-confirm-delete'); } },3500);
  });

  /* Global double-submit guard:
     On submit of any POST form, immediately disable its submit button(s) so a
     second click (or double/triple-click) cannot send the request again.
     GET filter forms and forms with data-no-guard are skipped. Small inline
     buttons (btn-sm) keep their label; larger/primary buttons show a busy label. */
  document.addEventListener('submit',function(e){
    const form=e.target;
    if(!form || form.tagName!=='FORM') return;
    if((form.getAttribute('method')||'get').toLowerCase()==='get') return;
    if(form.hasAttribute('data-no-guard')) return;
    form.querySelectorAll('button[type="submit"],button:not([type]),input[type="submit"]').forEach(function(btn){
      if(btn.disabled) return;
      btn.disabled=true;
      btn.classList.add('is-busy');
      if(!btn.classList.contains('btn-sm') && btn.tagName==='BUTTON'){
        if(!btn.dataset.label) btn.dataset.label=btn.textContent;
        btn.textContent='Please wait…';
      }
    });
  },true);

  /* Theme accent swatches on Settings page */
  document.querySelectorAll('[data-accent-swatch]').forEach(function(sw){
    sw.addEventListener('click',function(){
      const input=document.getElementById('accent_color');
      if(input){ input.value=sw.getAttribute('data-accent-swatch'); }
      document.querySelectorAll('[data-accent-swatch]').forEach(s=>s.classList.remove('selected'));
      sw.classList.add('selected');
    });
  });
});

/* Chart.js theme helper: colors follow the resolved theme (dark/light). */
window.mdosChartTheme=function(){
  const light=document.documentElement.getAttribute('data-theme-resolved')==='light';
  const accent=getComputedStyle(document.documentElement).getPropertyValue('--accent').trim()||'#8BCB9B';
  return {
    accent:accent,
    tick: light ? '#4A5A50' : '#A9BCAE',
    grid: light ? 'rgba(20,32,26,0.10)' : 'rgba(169,188,174,0.15)',
    palette:['#8BCB9B','#E7B765','#7FB3D5','#D87979','#A8B5AA','#5E8C6A','#C9A86A']
  };
};
window.mdosApplyChartDefaults=function(){
  if(typeof Chart==='undefined') return;
  const t=window.mdosChartTheme();
  Chart.defaults.color=t.tick; Chart.defaults.borderColor=t.grid;
  Chart.defaults.plugins.legend.labels.boxWidth=12;
  Chart.defaults.plugins.legend.labels.font={size:12};
};

async function enablePush(){if(!('Notification' in window))return alert('Notifications not supported');const p=await Notification.requestPermission();if(p!=='granted')return; if('serviceWorker' in navigator){const reg=await navigator.serviceWorker.ready; try{const sub=await reg.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:document.querySelector('meta[name=vapid-public]')?.content}); await fetch('/settings/notifications/subscription',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(sub)});}catch(e){console.log(e);}}}
