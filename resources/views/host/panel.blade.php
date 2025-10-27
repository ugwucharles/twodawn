@extends('layouts.public')

@section('title', 'Host Panel')
@section('robots', 'noindex, nofollow')

@section('content')
<section id="page-content" class="py-8 sm:py-10">
  <style>
    @media (min-width: 1024px){ /* desktop only */
      #host-desktop-sidebar{ display:block !important; position:fixed; left:16px; top:1rem; bottom:1.5rem; width:16rem; z-index:40; }
      .host-desktop-content{ margin-left: 18rem !important; margin-right: 1.5rem !important; }
    }
  </style>
  <div class="max-w-6xl mx-auto px-6 host-desktop-content">
    <div class="flex items-start justify-between gap-6 mb-6">
      <div class="flex items-center gap-3">
<button id="host-menu-btn" class="md:hidden inline-flex items-center justify-center w-10 h-10 text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
          <h1 class="text-2xl font-extrabold">{{ $event->title }} — Host Panel</h1>
          <div class="text-zinc-400 text-sm mt-1">Token: {{ $host->label ?? 'Link' }} • Expires {{ optional($host->expires_at)->diffForHumans() }}</div>
        </div>
      </div>
      <div class="hidden"></div>
    </div>

    <!-- Desktop fixed left sidebar -->
    <aside id="host-desktop-sidebar" class="hidden">
      <div class="h-full rounded-2xl bg-white/5 ring-1 ring-white/10 p-4 overflow-auto">
        <div class="text-xs text-zinc-400 mb-2">Menu</div>
        <div class="grid grid-cols-3 gap-3 text-center mb-3">
          <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-2"><div class="text-[11px] text-zinc-400">Sold</div><div class="text-xl font-bold">{{ $sold }}</div></div>
          <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-2"><div class="text-[11px] text-zinc-400">Checked</div><div class="text-xl font-bold menu-checked">{{ $checked }}</div></div>
          <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-2"><div class="text-[11px] text-zinc-400">Remain</div><div class="text-xl font-bold menu-remaining">{{ $remaining }}</div></div>
        </div>
        <nav class="grid gap-2 text-sm">
          <a href="#scan" data-goto="#scan" class="rounded px-3 py-2 hover:bg-white/5">Scan</a>
          <a href="{{ route('host.people', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5">Scanned people</a>
          <button data-copy-link class="text-left rounded px-3 py-2 hover:bg-white/5">Copy my link</button>
        </nav>
      </div>
    </aside>

    <!-- Mobile side menu -->
    <div id="host-menu-overlay" class="hidden fixed inset-0 bg-black/50 z-50"></div>
    <aside id="host-menu" class="hidden fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-zinc-950/95 ring-1 ring-white/10 z-50 p-6">
      <div class="flex items-center justify-between mb-4">
        <div class="font-semibold">Host Panel</div>
        <button id="host-menu-close" class="text-zinc-400 hover:text-white">Close</button>
      </div>
      <div class="grid grid-cols-3 gap-3 text-center mb-4">
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Sold</div><div class="text-2xl font-bold">{{ $sold }}</div></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Checked</div><div class="text-2xl font-bold menu-checked">{{ $checked }}</div></div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3"><div class="text-xs text-zinc-400">Remaining</div><div class="text-2xl font-bold menu-remaining">{{ $remaining }}</div></div>
      </div>
      <nav class="grid gap-2 text-sm">
        <a href="#scan" data-goto="#scan" class="rounded px-3 py-2 hover:bg-white/5">Scan</a>
        <a href="#manual" data-goto="#manual" class="rounded px-3 py-2 hover:bg-white/5">Manual entry</a>
        <a href="#recent-card" data-goto="#recent-card" class="rounded px-3 py-2 hover:bg-white/5">Recent scans</a>
        <a href="{{ route('host.people', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5">Scanned people</a>
        <button data-copy-link class="text-left rounded px-3 py-2 hover:bg-white/5">Copy my link</button>
      </nav>
    </aside>


    <div class="md:flex md:gap-6">
      <!-- Desktop sidebar -->
      <aside class="hidden md:block w-64 shrink-0 md:sticky md:top-24">
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
          <div class="text-xs text-zinc-400 mb-2">Menu</div>
          <div class="grid grid-cols-3 gap-3 text-center mb-3">
            <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-2"><div class="text-[11px] text-zinc-400">Sold</div><div class="text-xl font-bold">{{ $sold }}</div></div>
            <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-2"><div class="text-[11px] text-zinc-400">Checked</div><div class="text-xl font-bold menu-checked">{{ $checked }}</div></div>
            <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-2"><div class="text-[11px] text-zinc-400">Remain</div><div class="text-xl font-bold menu-remaining">{{ $remaining }}</div></div>
          </div>
          <nav class="grid gap-2 text-sm">
            <a href="#scan" data-goto="#scan" class="rounded px-3 py-2 hover:bg-white/5">Scan</a>
            <a href="{{ route('host.people', $host->token) }}" class="rounded px-3 py-2 hover:bg-white/5">Scanned people</a>
            <button data-copy-link class="text-left rounded px-3 py-2 hover:bg-white/5">Copy my link</button>
          </nav>
        </div>
      </aside>

      <!-- Main content -->
      <div class="flex-1">
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

          <!-- manual entry section kept -->
          <div id="manual" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4 lg:col-start-1">
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
          <div id="recent-card" class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4 lg:col-start-2">
            <div class="flex items-center justify-between mb-2">
              <div class="text-sm text-zinc-300">Recent scans</div>
              <button id="clear-recent" class="text-xs text-zinc-400 hover:text-white">Clear</button>
            </div>
            <ul id="recent" class="mt-1 text-sm text-zinc-300 space-y-1"></ul>
            <div class="mt-2 text-xs text-zinc-500">Latest results on this device only.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Scan result modal -->
  <div id="scan-modal" class="fixed inset-0 z-50 hidden">
    <style>
      @keyframes bounceIn { 0%{transform:scale(.92);opacity:.0} 60%{transform:scale(1.04);opacity:1} 100%{transform:scale(1)} }
      .animate-bounceIn{ animation: bounceIn .28s ease-out both; }
      html.scan-modal-open { overflow: hidden; overscroll-behavior: none; }
      html.scan-modal-open #page-content > *:not(#scan-modal){ pointer-events: none; filter: blur(12px); }
      html.scan-modal-open #scan-modal { pointer-events: auto; }
    </style>
    <div id="scan-modal-overlay" class="absolute inset-0 bg-black/80 backdrop-blur-2xl z-0"></div>
    <div class="absolute inset-0 z-10 flex items-center justify-center p-4 pointer-events-auto">
      <div id="scan-modal-card" class="rounded-2xl bg-black ring-1 ring-white/10 p-8 text-center shadow-2xl w-full max-w-xl">
        <div id="scan-modal-badge" class="mx-auto mb-3 inline-flex items-center px-3 py-1.5 rounded-full text-xs"></div>
        <h3 id="scan-modal-title" class="text-2xl font-extrabold"></h3>
        <p id="scan-modal-sub" class="mt-2 text-zinc-300"></p>
        <p id="scan-modal-meta" class="mt-2 text-zinc-500 text-xs"></p>
        <p id="scan-modal-remaining" class="mt-3 text-zinc-400 text-sm"></p>
        <button id="scan-modal-close" class="mt-5 px-5 py-2 rounded-md bg-white text-black font-medium">Close</button>
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

function clearDemo(){ recent.querySelectorAll('[data-demo]')?.forEach(el=>el.remove()); }
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

// Modal helpers
const modalEl = document.getElementById('scan-modal');
const modalCard = document.getElementById('scan-modal-card');
const modalOverlay = document.getElementById('scan-modal-overlay');
const modalClose = document.getElementById('scan-modal-close');
const modalBadge = document.getElementById('scan-modal-badge');
const modalTitle = document.getElementById('scan-modal-title');
const modalSub = document.getElementById('scan-modal-sub');
const modalMeta = document.getElementById('scan-modal-meta');
const modalRem = document.getElementById('scan-modal-remaining');
let modalTimer = null;
// Robust scroll lock
let __scrollY = 0;
function lockScroll(){
  __scrollY = window.scrollY || document.documentElement.scrollTop || 0;
  document.documentElement.classList.add('scan-modal-open');
  document.documentElement.style.overflow='hidden';
  document.documentElement.style.overscrollBehavior='none';
  document.body.style.overflow='hidden';
  document.body.style.position='fixed';
  document.body.style.top = `-${__scrollY}px`;
  document.body.style.width='100%';
}
function unlockScroll(){
  document.body.style.position='';
  document.body.style.top='';
  document.body.style.width='';
  document.body.style.overflow='';
  document.documentElement.style.overflow='';
  document.documentElement.style.overscrollBehavior='';
  document.documentElement.classList.remove('scan-modal-open');
  window.scrollTo(0, __scrollY||0);
}
function closeScanModal(){ modalEl.classList.add('hidden'); if(modalTimer){ clearTimeout(modalTimer); modalTimer=null;} unlockScroll(); }
function openScanModal(kind, opts){
  modalBadge.className = 'mx-auto mb-2 inline-flex items-center px-3 py-1.5 rounded-full text-xs';
  if (kind==='ok') modalBadge.classList.add('bg-emerald-500/20','text-emerald-300','ring-1','ring-emerald-500/30');
  else if (kind==='warn') modalBadge.classList.add('bg-yellow-500/20','text-yellow-300','ring-1','ring-yellow-500/30');
  else modalBadge.classList.add('bg-red-500/20','text-red-300','ring-1','ring-red-500/30');
  modalBadge.textContent = (kind==='ok' ? 'Valid ticket' : kind==='warn' ? 'Already checked in' : 'Invalid ticket');
  modalTitle.textContent = opts?.title || '';
  modalSub.textContent = opts?.sub || '';
  modalMeta.textContent = opts?.last ? (`Last check-in: ` + new Date(opts.last).toLocaleString()) : '';
  modalRem.textContent = opts?.remaining != null ? `Tickets left on this order: ${opts.remaining}` : '';
  modalEl.classList.remove('hidden');
  modalCard.classList.remove('animate-bounceIn'); // retrigger
  void modalCard.offsetWidth; modalCard.classList.add('animate-bounceIn');
  if (modalTimer) clearTimeout(modalTimer);
  lockScroll();
}
// Keep modal open unless Close is clicked
modalOverlay.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); });
modalEl.addEventListener('click', (e)=>{ if(e.target === modalEl){ e.preventDefault(); e.stopPropagation(); } });
modalClose.addEventListener('click', closeScanModal);
// Prevent scroll on overlay for iOS
const preventScroll = (ev)=>{ ev.preventDefault(); };
modalEl.addEventListener('wheel', preventScroll, { passive:false });
modalEl.addEventListener('touchmove', preventScroll, { passive:false });
// Disable ESC-to-close: no handler for modal

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


async function verify(text, source='camera'){
  try{
    const res = await fetch(verifyUrl, { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}, body: JSON.stringify({ text, source }) });
    const data = await res.json();
if (!res.ok){ setBadge('err', data?.message || 'Link expired or invalid'); openScanModal('err',{ title:'Link issue', sub:data?.message||'' }); notify('err'); return; }
    if (!data.valid){ const kind = data.already?'warn':'err'; setBadge(kind, data.already?`Already checked in • ${data.event?.title} • ${data.buyer?.name}`:'Invalid ticket'); openScanModal(kind,{ title: data.already? 'Already checked in' : 'Invalid ticket', sub: `${data.buyer?.name || ''} • ${data.event?.title || ''}`, last: data.last_checkin_at }); addRecent(kind, data.already?`Already • ${data.buyer?.name || ''}`:'Invalid'); notify(kind); return; }
    setBadge('ok', `Valid • ${data.event?.title} • ${data.buyer?.name}`); openScanModal('ok',{ title:'Valid ticket', sub:`${data.buyer?.name} • ${data.event?.title}`, remaining: data.remaining, last: data.last_checkin_at }); notify('ok');
    const rem = parseInt(data.remaining || 0,10);
    statChecked.textContent = String(parseInt(statChecked.textContent||'0',10) + 1);
    statRemaining.textContent = String(rem >= 0 ? rem : 0);
    document.querySelectorAll('.menu-checked').forEach(el => { try { el.textContent = String(parseInt(el.textContent||'0',10) + 1); } catch(_) { el.textContent = '—'; } });
    document.querySelectorAll('.menu-remaining').forEach(el => { el.textContent = String(rem >= 0 ? rem : 0); });
    addRecent('ok', `OK • ${data.buyer?.name}`);
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

document.querySelectorAll('[data-copy-link]')?.forEach(el=>{
  el.addEventListener('click', async ()=>{ try{ await navigator.clipboard.writeText(location.href); alert('Link copied'); } catch{ alert(location.href); } });
});

startScanner();

// Clear buttons
 document.getElementById('clear-recent')?.addEventListener('click', ()=>{ recent.innerHTML=''; });

document.getElementById('manual-submit').addEventListener('click', ()=>{
  const v = document.getElementById('manual-code').value.trim(); if(v) verify(v,'manual');
});
</script>
@endsection
