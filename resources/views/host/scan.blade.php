@extends('layouts.public')

@section('title', 'Scanner | ' . ($event->title ?? 'Event'))
@section('chat','off')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="mt-[9px] pb-10">
  <div class="max-w-6xl mx-auto px-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
      <div>
        <h1 class="text-2xl font-extrabold">Scanner — {{ $event->title }}</h1>
        <div class="text-zinc-400 text-sm mt-1">{{ $host->label ? ('Token: '.$host->label.' • ') : '' }}Expires {{ optional($host->expires_at)->diffForHumans() }}</div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button type="button" data-copy-link class="inline-flex items-center px-3 py-2 rounded-md bg-white text-black text-sm hover:bg-zinc-100">Copy link</button>
      </div>
    </div>

    <div class="mt-6 grid lg:grid-cols-2 gap-6 items-start">
      <!-- Camera scanner -->
      <div class="lg:col-span-2 rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="flex items-center justify-between mb-3">
          <div class="text-sm text-zinc-300">Camera scanner</div>
          <div class="text-xs text-zinc-400">Auto-start</div>
        </div>
        <div id="qr-reader" class="rounded-xl overflow-hidden bg-black relative" style="width:100%; height:60vh; max-height:520px; min-height:300px">
          <div id="scan-error" class="absolute inset-0 hidden items-center justify-center text-center text-sm text-red-300 px-4"></div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
          <button id="btn-switch" class="px-3 py-1.5 rounded-md bg-white/10 ring-1 ring-white/10 text-sm hover:bg-white/20">Switch camera</button>
          <button id="btn-pause" class="px-3 py-1.5 rounded-md bg-white/10 ring-1 ring-white/10 text-sm hover:bg-white/20">Pause</button>
          <button id="btn-resume" class="hidden px-3 py-1.5 rounded-md bg-white text-black text-sm hover:bg-zinc-100">Resume</button>
          <button id="btn-copy" class="px-3 py-1.5 rounded-md bg-white text-black text-sm hover:bg-zinc-100" data-copy-link>Copy link</button>
        </div>
        <div class="mt-2 text-xs text-zinc-400">Tips: Use the rear camera • Hold steady • Clean lens for faster scans.</div>
      </div>

      <!-- Manual entry -->
      <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="text-sm text-zinc-300 mb-2">Enter code manually</div>
        <form class="flex gap-2" onsubmit="return false;">
          <input id="manual-code" type="text" placeholder="Order reference (PA_...)" class="flex-1 rounded-md bg-black/30 border border-white/10 px-3 py-2 focus:border-white/30 focus:ring-0" />
          <button id="manual-submit" class="rounded-md px-4 py-2 bg-white text-black text-sm hover:bg-zinc-100">Verify</button>
        </form>
        <div id="result" class="mt-4 hidden">
          <div id="status-badge" class="inline-flex items-center px-2 py-1 rounded text-xs"></div>
          <div class="mt-2 text-sm" id="result-text"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Result modal (reuse from panel) -->
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
const statChecked = null; // not shown here
const statRemaining = null;
const recent = null; // no recent list on scanner page

let scannerRef = null;
let cameraDevices = [];
let cameraIndex = 0;
let scanningPaused = false;

function clearDemo(){ /* no-op on this page */ }
function addRecent(kind, text){ /* no-op */ }

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

// Modal helpers (copied from panel)
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
function lockScroll(){ document.documentElement.classList.add('scan-modal-open'); document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden'; }
function unlockScroll(){ document.documentElement.classList.remove('scan-modal-open'); document.documentElement.style.overflow=''; document.body.style.overflow=''; }
function closeScanModal(){ modalEl.classList.add('hidden'); if(modalTimer){ clearTimeout(modalTimer); modalTimer=null;} unlockScroll(); }
function openScanModal(kind, opts){
  modalBadge.className = 'mx-auto mb-2 inline-flex items-center px-3 py-1.5 rounded-full text-xs';
  if (kind==='ok') modalBadge.classList.add('bg-emerald-500/20','text-emerald-300','ring-1','ring-emerald-500/30');
  else if (kind==='warn') modalBadge.classList.add('bg-yellow-500/20','text-yellow-300','ring-1','ring-yellow-500/30');
  else modalBadge.classList.add('bg-red-500/20','text-red-300','ring-1','ring-red-500/30');
  modalBadge.textContent = (kind==='ok' ? 'Valid ticket' : kind==='warn' ? 'Already checked in' : 'Invalid ticket');
  modalTitle.textContent = opts?.title || '';
  modalSub.textContent = opts?.sub || '';
  modalMeta.textContent = opts?.last ? ('Last check-in: ' + new Date(opts.last).toLocaleString()) : '';
  modalRem.textContent = opts?.remaining != null ? ('Tickets left on this order: ' + opts.remaining) : '';
  modalEl.classList.remove('hidden');
  modalCard.classList.remove('animate-bounceIn'); void modalCard.offsetWidth; modalCard.classList.add('animate-bounceIn');
  lockScroll();
}
modalOverlay.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); });
modalEl.addEventListener('click', (e)=>{ if(e.target === modalEl){ e.preventDefault(); e.stopPropagation(); } });
modalClose.addEventListener('click', closeScanModal);

function notify(kind){ try{ if(navigator.vibrate){ if(kind==='ok') navigator.vibrate([30]); else if(kind==='warn') navigator.vibrate([20,40,20]); else navigator.vibrate([30,30,30]); } }catch{} }

async function verify(text, source='camera'){
  try{
    const res = await fetch(verifyUrl, { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}, body: JSON.stringify({ text, source }) });
    const data = await res.json();
    if (!res.ok){ setBadge('err', data?.message || 'Link expired or invalid'); openScanModal('err',{ title:'Link issue', sub:data?.message||'' }); notify('err'); return; }
    if (!data.valid){ const kind = data.already?'warn':'err'; setBadge(kind, data.already?`Already checked in • ${data.event?.title} • ${data.buyer?.name}`:'Invalid ticket'); openScanModal(kind,{ title: data.already? 'Already checked in' : 'Invalid ticket', sub: `${data.buyer?.name || ''} • ${data.event?.title || ''}`, last: data.last_checkin_at }); addRecent(kind, data.already?`Already • ${data.buyer?.name || ''}`:'Invalid'); notify(kind); return; }
    setBadge('ok', `Valid • ${data.event?.title} • ${data.buyer?.name}`); openScanModal('ok',{ title:'Valid ticket', sub:`${data.buyer?.name} • ${data.event?.title}`, remaining: data.remaining, last: data.last_checkin_at }); notify('ok');
  }catch{ setBadge('err','Network error'); notify('err'); }
}

async function loadScript(url){ return new Promise((res, rej)=>{ const s=document.createElement('script'); s.src=url; s.async=true; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); }
let h5qReady = null;
async function ensureHtml5Qrcode(){
  if (window.Html5Qrcode) return true;
  if (h5qReady) return h5qReady;
  const urls = [ @json(route('host.assets.h5qrcode')), 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.10/minified/html5-qrcode.min.js' ];
  h5qReady = (async () => { for (const u of urls){ try{ await loadScript(u); if (window.Html5Qrcode) return true; } catch(_){} } return false; })();
  return h5qReady;
}
async function ensureJsQR(){ if (window.jsQR) return true; try { await loadScript('https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js'); return !!window.jsQR; } catch(_) { return false; } }

async function startScanner(){
  const box = document.getElementById('qr-reader'); const errBox = document.getElementById('scan-error');
  try {
    const ok = await ensureHtml5Qrcode();
    if (ok && window.Html5Qrcode) {
      scannerRef = new Html5Qrcode('qr-reader');
      cameraDevices = await Html5Qrcode.getCameras();
      if (cameraDevices && cameraDevices.length) {
        cameraIndex = Math.max(0, cameraDevices.findIndex(d=>/back|rear|environment/i.test(d.label||'')));
        if (cameraIndex < 0) cameraIndex = 0;
        await scannerRef.start(cameraDevices[cameraIndex].id, { fps: 10, qrbox: Math.max(180, Math.min(340, Math.round((box.clientWidth||400)*0.8))) }, (txt)=>{ verify(txt,'camera'); }, ()=>{});
        document.getElementById('btn-switch')?.addEventListener('click', async ()=>{ try{ if (!cameraDevices.length) return; cameraIndex = (cameraIndex + 1) % cameraDevices.length; await scannerRef.stop().catch(()=>{}); await scannerRef.start(cameraDevices[cameraIndex].id, { fps: 10, qrbox: 250 }, (txt)=>{ verify(txt,'camera'); }, ()=>{}); }catch(_){}});
        const btnPause = document.getElementById('btn-pause'); const btnResume = document.getElementById('btn-resume');
        btnPause?.addEventListener('click', ()=>{ try { scannerRef.pause(true); scanningPaused=true; btnPause.classList.add('hidden'); btnResume.classList.remove('hidden'); } catch(_){}});
        btnResume?.addEventListener('click', ()=>{ try { scannerRef.resume(); scanningPaused=false; btnResume.classList.add('hidden'); btnPause.classList.remove('hidden'); } catch(_){}});
        return;
      }
    }
  } catch (_) {}
  try{
    if ('BarcodeDetector' in window) {
      const supported = (typeof BarcodeDetector.getSupportedFormats === 'function') ? await BarcodeDetector.getSupportedFormats() : ['qr_code'];
      if (!supported || !supported.includes('qr_code')) throw new Error('QR not supported');
      const det = new BarcodeDetector({ formats:['qr_code'] });
      const stream = await navigator.mediaDevices.getUserMedia({ video:{ facingMode:'environment' } });
      const v = document.createElement('video'); v.playsInline = true; v.muted = true; v.srcObject = stream; await v.play();
      box.innerHTML=''; box.appendChild(v); v.style.width='100%'; v.style.height='100%'; v.style.objectFit='cover';
      let last=''; const tick=async()=>{ try{ const codes=await det.detect(v); if(codes&&codes.length){ const t=codes[0].rawValue; if(t && t!==last){ last=t; verify(t,'camera'); setTimeout(()=>last='',1200);} } }catch{} requestAnimationFrame(tick); }; requestAnimationFrame(tick); return;
    }
  }catch{}
  try {
    const ok = await ensureJsQR();
    if (ok) {
      const stream = await navigator.mediaDevices.getUserMedia({ video:{ facingMode:'environment' } });
      const v = document.createElement('video'); v.playsInline = true; v.muted = true; v.srcObject = stream; await v.play();
      box.innerHTML=''; box.appendChild(v); v.style.width='100%'; v.style.height='100%'; v.style.objectFit='cover';
      const canvas = document.createElement('canvas'); const ctx = canvas.getContext('2d', { willReadFrequently: true });
      let last=''; const tick=()=>{ try { const W = box.clientWidth || 400, H = box.clientHeight || 300; canvas.width=W; canvas.height=H; ctx.drawImage(v,0,0,W,H); const img = ctx.getImageData(0,0,W,H); const qr = window.jsQR(img.data, img.width, img.height); if (qr && qr.data) { const t=qr.data; if (t!==last){ last=t; verify(t,'camera'); setTimeout(()=>last='',1200);} } } catch(_){} requestAnimationFrame(tick); }; requestAnimationFrame(tick); return;
    }
  } catch(_) {}
  if (errBox){ errBox.textContent = 'Camera unavailable. Allow permission or try another device.'; errBox.classList.remove('hidden'); errBox.classList.add('flex'); }
}

// Start
startScanner();

document.getElementById('manual-submit')?.addEventListener('click', ()=>{ const v = document.getElementById('manual-code').value.trim(); if(v) verify(v,'manual'); });
Array.from(document.querySelectorAll('[data-copy-link]')).forEach(el=>{ el.addEventListener('click', async ()=>{ try{ await navigator.clipboard.writeText(location.href); alert('Link copied'); } catch{ alert(location.href); } }); });
</script>
@endsection
