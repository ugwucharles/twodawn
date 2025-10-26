@extends('layouts.public')

@section('title', 'Host Panel')
@section('robots', 'noindex, nofollow')

@section('content')
<section class="py-8 sm:py-10">
  <div class="max-w-6xl mx-auto px-6">
    <div class="flex items-start justify-between gap-6 mb-6">
      <div>
        <h1 class="text-2xl font-extrabold">{{ $event->title }} — Host Panel</h1>
        <div class="text-zinc-400 text-sm mt-1">Token: {{ $host->label ?? 'Link' }} • Expires {{ optional($host->expires_at)->diffForHumans() }}</div>
      </div>
      <div class="grid grid-cols-3 gap-3 text-center">
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">
          <div class="text-xs text-zinc-400">Sold</div>
          <div class="text-2xl font-bold">{{ $sold }}</div>
        </div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">
          <div class="text-xs text-zinc-400">Checked in</div>
          <div class="text-2xl font-bold" id="stat-checked">{{ $checked }}</div>
        </div>
        <div class="rounded-xl bg-white/5 ring-1 ring-white/10 p-3">
          <div class="text-xs text-zinc-400">Remaining</div>
          <div class="text-2xl font-bold" id="stat-remaining">{{ $remaining }}</div>
        </div>
      </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 items-start">
      <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="flex items-center justify-between mb-3">
          <div class="text-sm text-zinc-300">Camera scan</div>
          <div class="text-xs text-zinc-400">Auto-start</div>
        </div>
        <div id="qr-reader" class="rounded-xl overflow-hidden bg-black relative" style="width:400px; height:400px; max-width:100%">
          <div id="scan-error" class="absolute inset-0 hidden items-center justify-center text-center text-sm text-red-300 px-4"></div>
        </div>
        <div class="mt-3 text-xs text-zinc-400">Grant camera permission; on mobile use the rear camera.</div>
      </div>

      <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
        <div class="text-sm text-zinc-300 mb-2">Enter code manually</div>
        <form class="flex gap-2" onsubmit="return false;">
          <input id="manual-code" type="text" placeholder="Order ref (PA_...)" class="flex-1 rounded-md bg-black/30 border border-white/10 px-3 py-2 focus:border-white/30 focus:ring-0" />
          <button id="manual-submit" class="rounded-md px-4 py-2 bg-white text-black text-sm hover:bg-zinc-100">Verify</button>
        </form>
        <div id="result" class="mt-4 hidden">
          <div id="status-badge" class="inline-flex items-center px-2 py-1 rounded text-xs"></div>
          <div class="mt-2 text-sm" id="result-text"></div>
        </div>
        <div class="mt-4">
          <div class="text-xs text-zinc-400">Recent scans</div>
          <ul id="recent" class="mt-1 text-sm text-zinc-300 space-y-1"></ul>
        </div>
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

function addRecent(text){ const li=document.createElement('li'); li.textContent = text; recent.prepend(li); while(recent.children.length>6) recent.removeChild(recent.lastChild); }

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

async function verify(text, source='camera'){
  try{
    const res = await fetch(verifyUrl, { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}, body: JSON.stringify({ text, source }) });
    const data = await res.json();
    if (!res.ok){ setBadge('err', data?.message || 'Link expired or invalid'); return; }
    if (!data.valid){ setBadge(data.already?'warn':'err', data.already?`Already checked in • ${data.event?.title} • ${data.buyer?.name}`:'Invalid ticket'); addRecent('Invalid'); return; }
    setBadge('ok', `Valid • ${data.event?.title} • ${data.buyer?.name}`);
    const rem = parseInt(data.remaining || 0,10);
    statChecked.textContent = String(parseInt(statChecked.textContent||'0',10) + 1);
    statRemaining.textContent = String(rem >= 0 ? rem : 0);
    addRecent(`OK • ${data.buyer?.name}`);
  }catch{ setBadge('err','Network error'); }
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
        await scanner.start(
          id,
          { fps: 10, qrbox: 250 }, // same settings as admin
          (txt)=>{ verify(txt,'camera'); },
          ()=>{}
        );
        return; // success
      }
    }
  } catch (_) { /* fall back below */ }

  // Fallback: Native BarcodeDetector if available and supports QR
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

  if (errBox){ errBox.textContent = 'Camera unavailable. Allow permission or try another device.'; errBox.classList.remove('hidden'); errBox.classList.add('flex'); }
}

startScanner();

document.getElementById('manual-submit').addEventListener('click', ()=>{
  const v = document.getElementById('manual-code').value.trim(); if(v) verify(v,'manual');
});
</script>
@endsection
