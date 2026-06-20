<x-organizer-layout>
<style>
/* =============================================
   3D CHECK MARK ANIMATION
============================================= */
@keyframes sceneEntrance {
  0%   { opacity:0; transform:scale(.85) translateY(20px); }
  60%  { opacity:1; transform:scale(1.04) translateY(-4px); }
  100% { opacity:1; transform:scale(1) translateY(0); }
}
@keyframes checkDraw {
  0%   { stroke-dashoffset: 200; }
  100% { stroke-dashoffset: 0; }
}
@keyframes circleSpin {
  0%   { transform: rotateY(-90deg) scale(.8); opacity:0; }
  60%  { transform: rotateY(10deg)  scale(1.06); opacity:1; }
  100% { transform: rotateY(0deg)   scale(1); opacity:1; }
}
@keyframes ringPulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(139,92,246,.45); }
  50%      { box-shadow: 0 0 0 22px rgba(139,92,246,0); }
}
@keyframes warnBounce {
  0%,100% { transform:scale(1);   }
  30%     { transform:scale(1.08); }
  60%     { transform:scale(.96);  }
}
@keyframes errShake {
  0%,100%{ transform:translateX(0); }
  20%    { transform:translateX(-10px); }
  40%    { transform:translateX(10px); }
  60%    { transform:translateX(-6px); }
  80%    { transform:translateX(6px); }
}
@keyframes scanLine {
  0%   { top:8%;  opacity:.8; }
  50%  { top:88%; opacity:1; }
  100% { top:8%;  opacity:.8; }
}
@keyframes cornerPulse {
  0%,100%{ opacity:.6; }
  50%    { opacity:1; }
}

.scene-enter   { animation: sceneEntrance .38s cubic-bezier(.22,.61,.36,1) both; }
.circle-spin   { animation: circleSpin .5s cubic-bezier(.22,.61,.36,1) .1s both; }
.ring-pulse    { animation: ringPulse 1.4s ease-in-out infinite; }
.check-draw    { stroke-dasharray:200; stroke-dashoffset:200; animation: checkDraw .45s ease-out .35s both; }
.warn-bounce   { animation: warnBounce .5s ease both; }
.err-shake     { animation: errShake .4s ease both; }
.scan-line     { animation: scanLine 2.4s ease-in-out infinite; }
.corner-pulse  { animation: cornerPulse 1.8s ease-in-out infinite; }

.scanner-modal {
  background: rgba(0,0,0,.82);
  backdrop-filter: blur(20px) saturate(1.8);
  -webkit-backdrop-filter: blur(20px) saturate(1.8);
}
</style>

<div id="page-content" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Header -->
  <div class="mb-8 flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-black text-gray-900 tracking-tight">QR Scanner</h1>
      <p class="text-gray-900 text-sm font-medium mt-1">Scan tickets for your events to check guests in.</p>
    </div>
    <div class="flex items-center gap-2">
      <span id="status-dot" class="inline-block w-2.5 h-2.5 rounded-full bg-gray-300 transition-colors duration-300"></span>
      <span id="status-label" class="ml-2 text-sm text-gray-600"></span>
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-6 items-start">

    <!-- Camera panel -->
    <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-6 shadow-sm">
      <div class="flex items-center gap-3 mb-4">
        <button id="btn-start"
                class="px-5 py-2.5 rounded-xl bg-purple-600 text-black border border-black text-sm font-bold hover:bg-purple-700 transition-colors shadow-md shadow-purple-200">
          Start Camera
        </button>
        <button id="btn-stop"
                class="px-5 py-2.5 rounded-xl bg-white border border-purple-200 text-gray-600 text-sm font-bold hover:bg-purple-50 transition-colors">
          Stop
        </button>
      </div>

      <!-- Video viewport -->
      <div class="relative rounded-2xl overflow-hidden bg-purple-600 aspect-video border border-black shadow-lg">
        <video id="video" playsinline autoplay muted class="w-full h-full object-cover"></video>

        <!-- Scan line -->
        <div id="scan-line-el"
             class="scan-line pointer-events-none absolute left-4 right-4 h-0.5 rounded-full bg-gradient-to-r from-transparent via-purple-400 to-transparent hidden"
             style="top:8%"></div>

        <!-- Corner brackets -->
        <div class="corner-pulse pointer-events-none absolute inset-0">
          <div class="absolute top-4 left-4 w-8 h-8 border-t-2 border-l-2 border-purple-400 rounded-tl-lg"></div>
          <div class="absolute top-4 right-4 w-8 h-8 border-t-2 border-r-2 border-purple-400 rounded-tr-lg"></div>
          <div class="absolute bottom-4 left-4 w-8 h-8 border-b-2 border-l-2 border-purple-400 rounded-bl-lg"></div>
          <div class="absolute bottom-4 right-4 w-8 h-8 border-b-2 border-r-2 border-purple-400 rounded-br-lg"></div>
        </div>

        <!-- Idle overlay -->
        <div id="cam-idle" class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-white">
          <svg class="w-14 h-14 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
          </svg>
          <p class="text-sm font-extrabold tracking-wide text-black">Press "Start Camera" to begin</p>
        </div>
      </div>
    </div>

    <!-- Manual entry + image upload -->
    <div class="space-y-4">
      <!-- Manual entry -->
      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-6 shadow-sm">
        <p class="text-sm font-bold text-black mb-3">Enter reference manually</p>
        <div class="flex gap-2">
          <input id="code-input" type="text" placeholder="PA_..."
                 class="flex-1 rounded-xl border border-purple-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400" />
          <button id="btn-verify"
                class="px-5 py-2.5 rounded-xl bg-purple-600 text-black border border-black text-sm font-bold hover:bg-purple-700 transition-colors shadow-md shadow-purple-200">
            Verify
          </button>
        </div>
      </div>

      <!-- Image upload -->
      <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-6 shadow-sm">
        <p class="text-sm font-bold text-black mb-3">Or upload a QR screenshot</p>
        <input id="file-input" type="file" accept="image/*" class="hidden" />
        <div id="dropzone"
             class="rounded-2xl border-2 border-dashed border-purple-200 bg-white hover:border-purple-400 transition-colors p-8 flex flex-col items-center justify-center cursor-pointer gap-3">
          <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-900">Drop image here or <span class="text-purple-600 underline">click to browse</span></p>
        </div>
      </div>

      <!-- Inline status -->
      <div id="inline-result" class="hidden bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-5 shadow-sm">
        <div id="inline-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-2"></div>
        <p id="inline-text" class="text-sm text-black font-medium"></p>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     SCAN RESULT MODAL
============================================================ -->
<div id="scan-modal" class="scanner-modal fixed inset-0 z-[70] hidden flex items-center justify-center p-4">

  <div id="modal-card"
       class="scene-enter relative w-full max-w-md rounded-3xl bg-zinc-950 border border-white/10 p-8 text-center shadow-2xl overflow-hidden">

    <!-- Subtle background glow -->
    <div id="modal-glow"
         class="pointer-events-none absolute inset-0 rounded-3xl opacity-0 transition-opacity duration-700"
         style="background:radial-gradient(ellipse at 50% 30%, rgba(139,92,246,.18) 0%, transparent 70%)"></div>

    <!-- 3D Animated icon wrapper -->
    <div class="relative mb-6 flex items-center justify-center" style="perspective:600px">
      <div id="icon-3d" class="relative flex items-center justify-center w-28 h-28">

        <!-- Ring -->
        <div id="icon-ring"
             class="circle-spin ring-pulse absolute inset-0 rounded-full"
             style="background:conic-gradient(from 220deg,#8b5cf6 0%,#6d28d9 40%,#4c1d95 70%,#8b5cf6 100%)"></div>

        <!-- Inner circle -->
        <div class="absolute inset-2 rounded-full bg-zinc-950"></div>

        <!-- Check SVG -->
        <svg id="icon-check" class="relative w-14 h-14 hidden" viewBox="0 0 52 52" fill="none">
          <circle cx="26" cy="26" r="24" stroke="#8b5cf6" stroke-width="2" fill="none" opacity=".3"/>
          <polyline class="check-draw" points="14,27 22,36 38,18" stroke="#a78bfa" stroke-width="4"
                    stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <!-- Warning icon -->
        <svg id="icon-warn" class="relative w-14 h-14 hidden warn-bounce" viewBox="0 0 52 52" fill="none">
          <circle cx="26" cy="26" r="24" stroke="#f59e0b" stroke-width="2" fill="none" opacity=".3"/>
          <line x1="26" y1="16" x2="26" y2="30" stroke="#fbbf24" stroke-width="4" stroke-linecap="round"/>
          <circle cx="26" cy="37" r="2.5" fill="#fbbf24"/>
        </svg>

        <!-- Error icon -->
        <svg id="icon-err" class="relative w-14 h-14 hidden err-shake" viewBox="0 0 52 52" fill="none">
          <circle cx="26" cy="26" r="24" stroke="#ef4444" stroke-width="2" fill="none" opacity=".3"/>
          <line x1="17" y1="17" x2="35" y2="35" stroke="#f87171" stroke-width="4" stroke-linecap="round"/>
          <line x1="35" y1="17" x2="17" y2="35" stroke="#f87171" stroke-width="4" stroke-linecap="round"/>
        </svg>

      </div>
    </div>

    <!-- Title -->
    <h3 id="modal-title" class="text-2xl font-black text-white mb-1"></h3>

    <!-- Buyer info (shown on success) -->
    <div id="modal-buyer" class="hidden mb-3">
      <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-2xl px-4 py-3 mt-1">
        <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <div class="text-left">
          <p id="modal-buyer-name" class="text-sm font-bold text-white leading-tight"></p>
          <p id="modal-buyer-email" class="text-xs text-purple-300 font-medium"></p>
        </div>
      </div>
    </div>

    <!-- Sub text -->
    <p id="modal-sub" class="text-zinc-400 text-sm font-medium mb-1"></p>
    <p id="modal-meta" class="text-zinc-600 text-xs mb-6"></p>

    <!-- Close -->
    <button id="modal-close"
            class="mt-1 px-8 py-3 rounded-xl bg-white text-zinc-900 font-black text-sm hover:bg-purple-50 transition-colors w-full">
      Close
    </button>
  </div>
</div>

<script>
(function(){
  const VERIFY_URL   = @json(route('organizer.scanner.verify'));
  const CSRF_TOKEN   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  /* ── DOM refs ─────────────────────────────── */
  const video        = document.getElementById('video');
  const camIdle      = document.getElementById('cam-idle');
  const scanLineEl   = document.getElementById('scan-line-el');
  const statusDot    = document.getElementById('status-dot');
  const statusLabel  = document.getElementById('status-label');
  const inlineResult = document.getElementById('inline-result');
  const inlineBadge  = document.getElementById('inline-badge');
  const inlineText   = document.getElementById('inline-text');

  // Modal refs
  const modal        = document.getElementById('scan-modal');
  const modalCard    = document.getElementById('modal-card');
  const modalGlow    = document.getElementById('modal-glow');
  const modalTitle   = document.getElementById('modal-title');
  const modalSub     = document.getElementById('modal-sub');
  const modalMeta    = document.getElementById('modal-meta');
  const modalBuyer   = document.getElementById('modal-buyer');
  const buyerName    = document.getElementById('modal-buyer-name');
  const buyerEmail   = document.getElementById('modal-buyer-email');
  const modalClose   = document.getElementById('modal-close');
  const iconRing     = document.getElementById('icon-ring');
  const iconCheck    = document.getElementById('icon-check');
  const iconWarn     = document.getElementById('icon-warn');
  const iconErr      = document.getElementById('icon-err');

  /* ── State variables ────────────────────────── */
  let stream         = null;
  let running        = false;
  let detector       = null;
  let rafId          = null;

  /* ── Status bar ────────────────────────────── */
  function setStatus(msg, kind='idle'){
    statusLabel.textContent = msg;
    statusDot.className = 'inline-block w-2.5 h-2.5 rounded-full transition-colors duration-300 '
      + (kind==='scanning' ? 'bg-purple-400 animate-pulse'
       : kind==='ok'       ? 'bg-emerald-400'
       : kind==='warn'     ? 'bg-amber-400'
       : kind==='err'      ? 'bg-red-400'
       : 'bg-gray-300');
  }

  /* ── Inline result ──────────────────────────── */
  function showInline(kind, msg){
    inlineResult.classList.remove('hidden');
    inlineBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-2 '
      + (kind==='ok'   ? 'bg-emerald-100 text-emerald-700'
       : kind==='warn' ? 'bg-amber-100 text-amber-700'
       : 'bg-red-100 text-red-700');
    inlineBadge.textContent = kind==='ok' ? '✓ Valid' : kind==='warn' ? '⚠ Already Used' : '✕ Invalid';
    inlineText.textContent  = msg;
  }

  /* ── Modal ─────────────────────────────────── */
  function openModal(kind, opts={}){
    // Reset icons
    [iconCheck,iconWarn,iconErr].forEach(el=>el.classList.add('hidden'));
    // Remove old animation classes then re-add to trigger replay
    modalCard.classList.remove('scene-enter');
    void modalCard.offsetWidth;
    modalCard.classList.add('scene-enter');

    if(kind==='ok'){
      iconRing.style.background = 'conic-gradient(from 220deg,#8b5cf6 0%,#6d28d9 40%,#4c1d95 70%,#8b5cf6 100%)';
      modalGlow.style.background = 'radial-gradient(ellipse at 50% 30%,rgba(139,92,246,.22) 0%,transparent 70%)';
      modalGlow.style.opacity='1';
      iconCheck.classList.remove('hidden');
      iconCheck.querySelector('polyline').style.animation='none';
      void iconCheck.offsetWidth;
      iconCheck.querySelector('polyline').style.animation='';
    } else if(kind==='warn'){
      iconRing.style.background = 'conic-gradient(from 220deg,#f59e0b,#d97706,#b45309,#f59e0b)';
      modalGlow.style.background = 'radial-gradient(ellipse at 50% 30%,rgba(245,158,11,.18) 0%,transparent 70%)';
      modalGlow.style.opacity='1';
      iconWarn.classList.remove('hidden');
      iconWarn.style.animation='none'; void iconWarn.offsetWidth; iconWarn.style.animation='';
    } else {
      iconRing.style.background = 'conic-gradient(from 220deg,#ef4444,#dc2626,#b91c1c,#ef4444)';
      modalGlow.style.background = 'radial-gradient(ellipse at 50% 30%,rgba(239,68,68,.18) 0%,transparent 70%)';
      modalGlow.style.opacity='1';
      iconErr.classList.remove('hidden');
      iconErr.style.animation='none'; void iconErr.offsetWidth; iconErr.style.animation='';
    }

    modalTitle.textContent = opts.title  ?? '';
    modalSub.textContent   = opts.sub    ?? '';
    modalMeta.textContent  = opts.last   ? 'Last check-in: ' + new Date(opts.last).toLocaleString() : '';

    if(opts.buyer){
      modalBuyer.classList.remove('hidden');
      buyerName.textContent  = opts.buyer.name  ?? '';
      buyerEmail.textContent = opts.buyer.email ?? '';
    } else {
      modalBuyer.classList.add('hidden');
    }

    modal.classList.remove('hidden');
    document.body.style.overflow='hidden';
  }

  function closeModal(){
    modal.classList.add('hidden');
    document.body.style.overflow='';
  }

  modalClose?.addEventListener('click', closeModal);
  modal?.addEventListener('click', e=>{ if(e.target===modal) closeModal(); });

  /* ── Haptics ────────────────────────────────── */
  function vibrate(kind){
    try{
      if(!navigator.vibrate) return;
      if(kind==='ok')   navigator.vibrate([30]);
      else if(kind==='warn') navigator.vibrate([20,60,20]);
      else navigator.vibrate([30,30,30]);
    }catch(_){}
  }

  /* ── Verify API ─────────────────────────────── */
  async function verifyText(text){
    setStatus('Verifying…','scanning');
    try{
      const res  = await fetch(VERIFY_URL, {
        method:'POST', credentials:'same-origin',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept':'application/json',
          'X-Requested-With':'XMLHttpRequest',
        },
        body: JSON.stringify({ text })
      });
      const data = await res.json();

      if(!res.ok){
        const msg = data?.message ?? 'Unknown error.';
        setStatus('Error','err');
        showInline('err', msg);
        openModal('err',{ title:'Error', sub: msg });
        vibrate('err');
        return;
      }

      if(data.valid){
        setStatus('Approved','ok');
        showInline('ok', `${data.buyer?.name} checked in to ${data.event?.title}`);
        openModal('ok',{
          title: '✓ Check-in Approved!',
          sub:   data.event?.title ?? '',
          buyer: data.buyer,
          last:  data.last_checkin_at,
        });
        vibrate('ok');
      } else if(data.already){
        setStatus('Already used','warn');
        showInline('warn', `Ticket was already used. Buyer: ${data.buyer?.name} (${data.buyer?.email})`);
        openModal('warn',{
          title: 'Already Checked In',
          sub:   data.event?.title ?? '',
          buyer: data.buyer,
          last:  data.last_checkin_at,
        });
        vibrate('warn');
      } else {
        setStatus('Invalid','err');
        const msg = data.message ?? 'Ticket is not valid.';
        showInline('err', msg);
        openModal('err',{ title:'Invalid Ticket', sub: msg });
      }
    } catch(e) {
      console.error(e);
      setStatus('Error', 'err');
      showInline('err', 'Network error.');
    }
  }

  async function startCamera(){
  try{
    setStatus('Starting…','scanning');
    // Request camera with minimal constraints for broader compatibility
    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
    if(!stream) throw new Error('No camera found');
    video.srcObject = stream;
    await video.play();
    camIdle.classList.add('hidden');
    scanLineEl.classList.remove('hidden');
    running = true;
    setStatus('Scanning…','scanning');
    if('BarcodeDetector' in window){
      detector = new BarcodeDetector({ formats:['qr_code'] });
      scanNative();
    } else {
      await loadJsQR();
      scanCanvas();
    }
  }catch(e){
    console.error('Camera start error:', e);
    setStatus('Camera blocked: ' + (e?.message || e), 'err');
    showInline('err','Allow camera access, or use the image upload / manual entry.');
  }
}

  function stopCamera(){
    running=false;
    if(rafId){ cancelAnimationFrame(rafId); rafId=null; }
    if(video){ video.pause?.(); video.srcObject=null; }
    if(stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; }
    camIdle.classList.remove('hidden');
    scanLineEl.classList.add('hidden');
    setStatus('Ready');
  }

  async function loadJsQR(){
    if(window.jsQR) return;
    const urls=['https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js',
                'https://unpkg.com/jsqr@1.4.0/dist/jsQR.js'];
    for(const u of urls){
      try{
        await new Promise((res,rej)=>{ const s=document.createElement('script'); s.src=u; s.onload=res; s.onerror=rej; document.head.appendChild(s); });
        if(window.jsQR) return;
      }catch(_){}
    }
  }

  async function scanNative(){
    const tick=async()=>{
      if(!running) return;
      try{
        const codes=await detector.detect(video);
        if(codes?.length){ const txt=codes[0].rawValue; if(txt){ running=false; setStatus('Found!','ok'); await verifyText(txt); } }
      }catch(_){}
      rafId=requestAnimationFrame(tick);
    };
    rafId=requestAnimationFrame(tick);
  }

  function scanCanvas(){
    const canvas=document.createElement('canvas'); const ctx=canvas.getContext('2d');
    const W=480,H=360; canvas.width=W; canvas.height=H;
    const tick=()=>{
      if(!running) return;
      try{
        ctx.drawImage(video,0,0,W,H);
        const img=ctx.getImageData(0,0,W,H);
        const qr=window.jsQR(img.data,img.width,img.height);
        if(qr?.data){ running=false; setStatus('Found!','ok'); verifyText(qr.data); return; }
      }catch(_){}
      rafId=requestAnimationFrame(tick);
    };
    rafId=requestAnimationFrame(tick);
  }

  /* ── Image decode ────────────────────────────── */
  async function scanImage(file){
    if(!file) return;
    setStatus('Decoding image…','scanning');
    try{
      if('BarcodeDetector' in window){
        const det=new BarcodeDetector({formats:['qr_code']});
        const bmp=await createImageBitmap(file);
        const codes=await det.detect(bmp);
        if(codes?.length){ await verifyText(codes[0].rawValue); return; }
      }
      await loadJsQR();
      const img=new Image(); const url=URL.createObjectURL(file);
      await new Promise((res,rej)=>{img.onload=res;img.onerror=rej;img.src=url;});
      const canvas=document.createElement('canvas'); const ctx=canvas.getContext('2d');
      canvas.width=img.width; canvas.height=img.height; ctx.drawImage(img,0,0);
      const data=ctx.getImageData(0,0,canvas.width,canvas.height);
      URL.revokeObjectURL(url);
      const qr=window.jsQR&&window.jsQR(data.data,data.width,data.height);
      if(qr?.data){ await verifyText(qr.data); return; }
      setStatus('No QR found','err');
      showInline('err','Could not detect a QR code in that image.');
    }catch(e){ setStatus('Error','err'); showInline('err','Failed to decode image.'); }
  }

  /* ── Wire up UI ──────────────────────────────── */
  document.getElementById('btn-start').addEventListener('click', startCamera);
  document.getElementById('btn-stop').addEventListener('click', stopCamera);

  document.getElementById('btn-verify').addEventListener('click', async()=>{
    const v=document.getElementById('code-input').value.trim();
    if(v) await verifyText(v);
    else showInline('err','Please enter a reference code.');
  });

  document.getElementById('code-input').addEventListener('keydown', async e=>{
    if(e.key==='Enter'){ const v=e.target.value.trim(); if(v) await verifyText(v); }
  });

  const fileInput=document.getElementById('file-input');
  const dropzone =document.getElementById('dropzone');
  dropzone.addEventListener('click',()=>fileInput.click());
  dropzone.addEventListener('dragover',e=>{ e.preventDefault(); dropzone.classList.add('border-purple-500'); });
  dropzone.addEventListener('dragleave',()=>dropzone.classList.remove('border-purple-500'));
  dropzone.addEventListener('drop',async e=>{ e.preventDefault(); dropzone.classList.remove('border-purple-500'); const f=e.dataTransfer.files[0]; if(f) await scanImage(f); });
  fileInput.addEventListener('change',async e=>{ const f=e.target.files[0]; if(f) await scanImage(f); });
})();
</script>
</x-organizer-layout>
