@extends('layouts.public')

@section('title', 'Host Panel')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-8 sm:py-10">
  <div class="max-w-6xl mx-auto px-6">
    <div class="flex items-start justify-between gap-6 mb-6">
      <div class="flex items-center gap-3">
        <button id="host-menu-btn" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white/10 ring-1 ring-white/15">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
          <h1 class="text-2xl font-extrabold">{{ $event->title }} — Host Panel</h1>
          <div class="text-zinc-400 text-sm mt-1">Token: {{ $host->label ?? 'Link' }} • Expires {{ optional($host->expires_at)->diffForHumans() }}</div>
        </div>
      </div>
      <!-- Hide tags on page; they appear inside the mobile menu instead -->
      <div class="hidden"></div>
    </div>

    <!-- Mobile side menu -->
    <div id="host-menu-overlay" class="hidden fixed inset-0 bg-black/50 z-50"></div>
    <aside id="host-menu" class="hidden fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-zinc-950/95 ring-1 ring-white/10 z-50 p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="font-semibold">Host Panel</div>
        <button id="host-menu-close" class="text-zinc-400 hover:text-white">Close</button>
      </div>
      <!-- Tags inside the menu -->
      <div class="grid grid-cols-3 gap-3 text-center mb-4">
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Sold</div><div class="text-2xl font-bold">{{ $sold }}</div></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Checked</div><div class="text-2xl font-bold" id="menu-checked">{{ $checked }}</div></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Remaining</div><div class="text-2xl font-bold" id="menu-remaining">{{ $remaining }}</div></div>
      </div>
      <nav class="grid gap-2 text-sm">
        <a href="#scan" data-goto="#scan" class="rounded px-3 py-2 hover:bg-white/5">Scan</a>
        <a href="#manual" data-goto="#manual" class="rounded px-3 py-2 hover:bg-white/5">Manual entry</a>
        <a href="#recent-card" data-goto="#recent-card" class="rounded px-3 py-2 hover:bg-white/5">Recent scans</a>
        <a href="#people-card" data-goto="#people-card" class="rounded px-3 py-2 hover:bg-white/5">Scanned people</a>
        <button id="copy-link" class="text-left rounded px-3 py-2 hover:bg-white/5">Copy my link</button>
      </nav>
    </aside>

    <div class="grid lg:grid-cols-2 gap-6 items-start">
      <div id="scan" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="flex items-center justify-between mb-3">
          <div class="text-sm text-zinc-300">Camera scan</div>
          <div class="text-xs text-zinc-400">Auto-start</div>
        </div>
        <div id="qr-reader" class="rounded-xl overflow-hidden bg-black relative" style="width:100%; height:60vh; max-height:480px; min-height:280px">
          <div id="scan-error" class="absolute inset-0 hidden items-center justify-center text-center text-sm text-red-300 px-4"></div>
        </div>
        <div class="mt-3 text-xs text-zinc-400">Grant camera permission; on mobile use the rear camera.</div>
      </div>

      <div id="manual" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="text-sm text-zinc-300 mb-2">Enter code manually</div>
        <form class="flex gap-2" onsubmit="return false;">
          <input id="manual-code" type="text" placeholder="Order ref (PA_...)" class="flex-1 rounded-md bg-black/30 border border-white/10 px-3 py-2 focus:border-white/30 focus:ring-0" />
          <button id="manual-submit" class="rounded-md px-4 py-2 bg-white text-black text-sm hover:bg-zinc-100">Verify</button>
        </form>
        <div id="result" class="mt-4 hidden">
          <div id="status-badge" class="inline-flex items-center px-2 py-1 rounded text-xs"></div>
          <div class="mt-2 text-sm" id="result-text"></div>
        </div>
      </div>

      <!-- Recent scans -->
      <div id="recent-card" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4 lg:col-start-1">
        <div class="flex items-center justify-between mb-2">
          <div class="text-sm text-zinc-300">Recent scans</div>
          <button id="clear-recent" class="text-xs text-zinc-400 hover:text-white">Clear</button>
        </div>
        <ul id="recent" class="mt-1 text-sm text-zinc-300 space-y-1"></ul>
        <div class="mt-2 text-xs text-zinc-500">Latest results on this device only.</div>
      </div>

      <!-- Scanned people list -->
      <div id="people-card" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4 lg:col-start-2">
        <div class="flex items-center justify-between mb-2">
          <div class="text-sm text-zinc-300">Scanned people</div>
          <button id="clear-people" class="text-xs text-zinc-400 hover:text-white">Clear</button>
        </div>
        <ul id="people" class="mt-1 text-sm text-zinc-300 space-y-1"></ul>
        <div class="mt-2 text-xs text-zinc-500">Shows the most recent check-ins on this device.</div>
      </div>
    </div>
  </div>
</section>

<script>
const token = @json($host->token);
const verifyUrl = @json(url('/h/'.$host->token.'/verify'));
const statChecked = document.getElementById('stat-checked');
const statRemaining = document.getElementById('stat-remaining');
const recent = document.getElementById('recent');
const people = document.getElementById('people');

function clearDemo(){ recent.querySelectorAll('[data-demo]')?.forEach(el=>el.remove()); people.querySelectorAll('[data-demo]')?.forEach(el=>el.remove()); }
function addRecent(kind, text){
  clearDemo();
  const li=document.createElement('li');
  li.className='flex items-center gap-2';
  const dot=document.createElement('span');
  dot.className='w-2 h-2 rounded-full ' + (kind==='ok'?'bg-emerald-400':kind==='warn'?'bg-yellow-400':'bg-rose-400');
  const label=document.createElement('span'); label.textContent = text;
  const ts=document.createElement('span'); ts.className='ml-auto text-xs text-zinc-500'; ts.textContent = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
  li.append(dot,label,ts);
  recent.prepend(li);
  while(recent.children.length>6) recent.removeChild(recent.lastChild);
}

function setBadge(kind, msg){
  const box = document.getElementById('result'); box.classList.remove('hidden');
  const b = document.getElementById('status-badge'); const t = document.getElementById('result-text');
  b.className = 'inline-flex items-center px-2 py-1 rounded text-xs';
  if (kind==='ok') b.classList.add('bg-emerald-500/20','text-emerald-300','ring-1','ring-emerald-500/30');
  else if (kind==='warn') b.classList.add('bg-yellow-500/20','text-yellow-300','ring-1','ring-yellow-500/30');
  else b.classList.add('bg-red-500/20','text-red-300','ring-1','ring-red-500/30');
  b.textContent = (kind==='ok' ? 'OK' : kind==='warn' ? 'Already' : 'Invalid');
  t.textContent = msg;
}

function notify(kind){
  try {
    if (navigator.vibrate) {
      if (kind==='ok') navigator.vibrate([30]);
      else if (kind==='warn') navigator.vibrate([20,40,20]);
      else navigator.vibrate([30,30,30]);
    }
    const AC = window.AudioContext || window.webkitAudioContext; if (!AC) return;
    const ctx = new AC(); const o = ctx.createOscillator(); const g = ctx.createGain(); o.connect(g); g.connect(ctx.destination);
    o.type='sine'; const now=ctx.currentTime; const freq = (kind==='ok')?880:(kind==='warn')?520:240; o.frequency.setValueAtTime(freq, now);
    g.gain.setValueAtTime(0.0001, now); g.gain.exponentialRampToValueAtTime(0.12, now+0.02);
    const dur = (kind==='ok')?0.12:(kind==='warn')?0.18:0.22; o.start(); o.stop(now+dur); o.onended=()=>ctx.close();
  } catch(_) {}
}

function addPerson(name, email){
  const li = document.createElement('li');
  li.className = 'flex items-center gap-2';
  const nameEl = document.createElement('span'); nameEl.textContent = name || 'Guest';
  const emailEl = document.createElement('span'); emailEl.className='text-xs text-zinc-400'; emailEl.textContent = email || '';
  const ts = document.createElement('span'); ts.className='ml-auto text-xs text-zinc-500'; ts.textContent = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
  li.append(nameEl, emailEl, ts);
  people.prepend(li);
  while(people.children.length>8) people.removeChild(people.lastChild);
}

async function verify(text, source='camera'){
  try{
    const res = await fetch(verifyUrl, { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}, body: JSON.stringify({ text, source }) });
    const data = await res.json();
    if (!res.ok){ setBadge('err', data?.message || 'Link expired or invalid'); notify('err'); return; }
    if (!data.valid){ const kind = data.already?'warn':'err'; setBadge(kind, data.already?`Already checked in • ${data.event?.title} • ${data.buyer?.name}`:'Invalid ticket'); addRecent(kind, data.already?`Already • ${data.buyer?.name || ''}`:'Invalid'); notify(kind); return; }
    setBadge('ok', `Valid • ${data.event?.title} • ${data.buyer?.name}`); notify('ok');
    const rem = parseInt(data.remaining || 0,10);
    statChecked.textContent = String(parseInt(statChecked.textContent||'0',10) + 1);
    statRemaining.textContent = String(rem >= 0 ? rem : 0);
    // reflect in menu tags too
    const mc=document.getElementById('menu-checked'); if(mc) mc.textContent = statChecked.textContent;
    const mr=document.getElementById('menu-remaining'); if(mr) mr.textContent = statRemaining.textContent;
    addRecent('ok', `OK • ${data.buyer?.name}`);
    addPerson(data.buyer?.name, data.buyer?.email);
  }catch{ setBadge('err','Network error'); notify('err'); }
}

// Camera scanner with robust fallbacks (BarcodeDetector → html5-qrcode)
async function loadScript(url){ return new Promise((res, rej)=>{ const s=document.createElement('script'); s.src=url; s.async=true; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); }
let h5qReady = null;
async function ensureHtml5Qrcode(){
  if (window.Html5Qrcode) return true;
  if (h5qReady) return h5qReady;
  const urls = [
    @json(route('host.assets.h5qrcode')),
    'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
    'https://unpkg.com/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.10/html5-qrcode.min.js'
  ];
  h5qReady = (async () => { for (const u of urls){ try{ await loadScript(u); if (window.Html5Qrcode) return true; } catch(_){} } return false; })();
  return h5qReady;
}

async function ensureJsQR(){
  if (window.jsQR) return true;
  try { await loadScript('https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js'); return !!window.jsQR; } catch(_) { return false; }
}

async function startScanner(){
  const box = document.getElementById('qr-reader');
  const errBox = document.getElementById('scan-error');

  // Prefer the same library as Admin: html5-qrcode
  try {
    const ok = await ensureHtml5Qrcode();
    if (ok && window.Html5Qrcode) {
      const scanner = new Html5Qrcode('qr-reader');
      const devices = await Html5Qrcode.getCameras();
      if (devices && devices.length) {
        let id = devices.find(d=>/back|rear|environment/i.test(d.label||''))?.id || (devices[0].id);
    const r = box.getBoundingClientRect(); const size = Math.round(Math.min(r.width, (r.height||r.width)) * 0.8);
    await scanner.start(
      id,
      { fps: 10, qrbox: Math.max(180, Math.min(320, size)) }, // responsive box
          (txt)=>{ verify(txt,'camera'); },
          ()=>{}
        );
        return; // success
      }
    }
  } catch (_) { /* fall back below */ }

  // Fallback 1: Native BarcodeDetector if available and supports QR
  try{
    if ('BarcodeDetector' in window) {
      const supported = (typeof BarcodeDetector.getSupportedFormats === 'function') ? await BarcodeDetector.getSupportedFormats() : ['qr_code'];
      if (!supported || !supported.includes('qr_code')) throw new Error('QR not supported');
      const det = new BarcodeDetector({ formats:['qr_code'] });
      const stream = await navigator.mediaDevices.getUserMedia({ video:{ facingMode:'environment' } });
      const v = document.createElement('video'); v.playsInline = true; v.muted = true; v.srcObject = stream; await v.play();
      box.innerHTML=''; box.appendChild(v); v.style.width='100%'; v.style.height='100%'; v.style.objectFit='cover';
      let last='';
      const tick=async()=>{ try{ const codes=await det.detect(v); if(codes&&codes.length){ const t=codes[0].rawValue; if(t && t!==last){ last=t; verify(t,'camera'); setTimeout(()=>last='',1200); } } }catch{} requestAnimationFrame(tick); };
      requestAnimationFrame(tick);
      return; // success
    }
  }catch{}

  // Fallback 2: jsQR on canvas
  try {
    const ok = await ensureJsQR();
    if (ok) {
      const stream = await navigator.mediaDevices.getUserMedia({ video:{ facingMode:'environment' } });
      const v = document.createElement('video'); v.playsInline = true; v.muted = true; v.srcObject = stream; await v.play();
      box.innerHTML=''; box.appendChild(v); v.style.width='100%'; v.style.height='100%'; v.style.objectFit='cover';
      const canvas = document.createElement('canvas'); const ctx = canvas.getContext('2d');
      let last='';
      const tick=()=>{
        try {
          const W = box.clientWidth || 400, H = box.clientHeight || 300;
          canvas.width=W; canvas.height=H; ctx.drawImage(v,0,0,W,H);
          const img = ctx.getImageData(0,0,W,H); const qr = window.jsQR(img.data, img.width, img.height);
          if (qr && qr.data) { const t=qr.data; if (t!==last){ last=t; verify(t,'camera'); setTimeout(()=>last='',1200);} }
        } catch(_){}
        requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
      return;
    }
  } catch(_) {}

  if (errBox){ errBox.textContent = 'Camera unavailable. Allow permission or try another device.'; errBox.classList.remove('hidden'); errBox.classList.add('flex'); }
}

// (No demo rows for manual code card as requested)

// Mobile menu controls
const menu = document.getElementById('host-menu');
const overlay = document.getElementById('host-menu-overlay');
const openBtn = document.getElementById('host-menu-btn');
const closeBtn = document.getElementById('host-menu-close');
function openMenu(){ menu.classList.remove('hidden'); overlay.classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeMenu(){ menu.classList.add('hidden'); overlay.classList.add('hidden'); document.body.style.overflow=''; }
openBtn?.addEventListener('click', openMenu); closeBtn?.addEventListener('click', closeMenu); overlay?.addEventListener('click', closeMenu);
window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeMenu(); });
Array.from(document.querySelectorAll('[data-goto]')).forEach(a=>{
  a.addEventListener('click', (e)=>{ e.preventDefault(); const id=a.getAttribute('data-goto'); const el=document.querySelector(id); if(el){ el.scrollIntoView({behavior:'smooth', block:'start'}); } closeMenu(); });
});

document.getElementById('copy-link')?.addEventListener('click', async ()=>{ try{ await navigator.clipboard.writeText(location.href); alert('Link copied'); } catch{ alert(location.href); } });

startScanner();

// Clear buttons
 document.getElementById('clear-recent')?.addEventListener('click', ()=>{ recent.innerHTML=''; });
 document.getElementById('clear-people')?.addEventListener('click', ()=>{ people.innerHTML=''; });

document.getElementById('manual-submit').addEventListener('click', ()=>{
  const v = document.getElementById('manual-code').value.trim(); if(v) verify(v,'manual');
});
</script>
@endsection
