<x-app-layout>
  <div id="page-content" class="py-6">
    <div class="max-w-6xl mx-auto px-6">
      <div class="mb-6 flex items-center">
<h1 class="text-3xl font-extrabold tracking-tight text-white">QR Scanner</h1>
      </div>

      <div class="grid lg:grid-cols-2 gap-6 items-start">
        <!-- Camera & preview -->
        <div class="rounded-2xl bg-zinc-900/40 backdrop-blur-sm ring-1 ring-white/10 p-5 shadow-xl">
          <div class="flex items-center gap-3 mb-3">
            <button id="btn-start" class="px-4 py-2 rounded-lg bg-indigo-500 text-white text-sm hover:bg-indigo-400 shadow-sm">Start camera</button>
            <button id="btn-stop" class="px-4 py-2 rounded-lg bg-zinc-800 ring-1 ring-white/10 text-white text-sm hover:bg-zinc-700">Stop</button>
            <div class="ml-auto flex items-center gap-2">
              <span id="status-dot" class="inline-block w-2 h-2 rounded-full bg-zinc-500"></span>
              <span id="status" class="inline-flex items-center rounded-full px-2 py-1 text-[11px] font-medium bg-white/10 ring-1 ring-white/10 text-zinc-300">Ready</span>
            </div>
          </div>
          <div class="aspect-video rounded-xl overflow-hidden bg-black/90 ring-1 ring-white/10 relative">
            <video id="video" playsinline autoplay muted class="w-full h-full object-cover"></video>
            <div class="pointer-events-none absolute inset-0">
              <div class="absolute inset-6 rounded-xl ring-1 ring-white/20"></div>
              <div class="absolute inset-0 grid grid-cols-3 grid-rows-3">
                <div class="border-r border-b border-white/5"></div><div class="border-r border-b border-white/5"></div><div class="border-b border-white/5"></div>
                <div class="border-r border-b border-white/5"></div><div class="border-r border-b border-white/5"></div><div class="border-b border-white/5"></div>
                <div class="border-r border-white/5"></div><div class="border-r border-white/5"></div><div></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Manual + Image -->
        <div class="rounded-2xl bg-zinc-900/40 backdrop-blur-sm ring-1 ring-white/10 p-5 shadow-xl">
          <div class="space-y-4">
            <div>
              <div class="text-sm text-zinc-300 mb-2">Enter code manually</div>
              <div class="flex gap-2">
                <input id="code" type="text" placeholder="Order ref (PA_...)" class="flex-1 rounded-lg bg-black/30 border border-white/10 px-3 py-2 focus:border-indigo-300 focus:ring-0" />
                <button id="btn-verify" class="rounded-lg px-4 py-2 bg-indigo-500 text-white text-sm hover:bg-indigo-400 shadow-sm">Verify</button>
              </div>
            </div>
            <div>
              <div class="text-sm text-zinc-300 mb-2">Or upload a screenshot</div>
              <input id="file" type="file" accept="image/*" class="hidden" />
              <div id="dropzone" class="relative rounded-2xl border-2 border-dashed border-white/15 bg-black/20 hover:border-indigo-400 transition p-6 flex items-center justify-center text-center cursor-pointer">
                <div>
                  <div class="mx-auto w-12 h-12 rounded-full bg-indigo-500/20 ring-1 ring-indigo-500/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-300" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14m-7-7h14"/></svg>
                  </div>
                  <div class="mt-3 text-sm">Drop image to scan, or <span class="underline">click to choose</span></div>
                  <div id="file-hint" class="mt-1 text-xs text-zinc-400">PNG/JPG from the success page</div>
                </div>
              </div>
            </div>

            <div class="pt-2">
              <div id="result" class="hidden">
                <div id="badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-white/10 ring-1 ring-white/15 text-zinc-300">Ready</div>
                <div id="text" class="mt-2 text-sm"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scan result modal -->
  <div id="scan-modal" class="fixed inset-0 z-[60] hidden">
    <style>
      @keyframes bounceIn { 0%{transform:scale(.92);opacity:.0} 60%{transform:scale(1.04);opacity:1} 100%{transform:scale(1)} }
      .animate-bounceIn{ animation: bounceIn .28s ease-out both; }
      html.scan-modal-open { overflow: hidden; overscroll-behavior: none; }
      html.scan-modal-open #page-content { pointer-events: none; }
      html.scan-modal-open #page-content > :not(#scan-modal){ filter: blur(12px); }
    </style>
    <div id="scan-modal-overlay" class="absolute inset-0 bg-black/80 backdrop-blur-2xl z-0"></div>
    <div class="absolute inset-0 z-10 flex items-center justify-center p-4">
      <div id="scan-modal-card" class="rounded-2xl bg-black ring-1 ring-white/10 p-8 text-center shadow-2xl w-full max-w-xl">
        <div id="scan-modal-badge" class="mx-auto mb-3 inline-flex items-center px-3 py-1.5 rounded-full text-xs"></div>
        <h3 id="scan-modal-title" class="text-2xl font-extrabold"></h3>
        <p id="scan-modal-sub" class="mt-2 text-zinc-300"></p>
        <p id="scan-modal-meta" class="mt-2 text-zinc-500 text-xs"></p>
        <button id="scan-modal-close" class="mt-5 px-5 py-2 rounded-md bg-white text-black font-medium">Close</button>
      </div>
    </div>
  </div>

  <script>
    // Utilities
    function setBadge(kind, msg){
      const r = document.getElementById('result'); r.classList.remove('hidden');
      const b = document.getElementById('badge'); const t = document.getElementById('text');
      b.className = 'inline-flex items-center px-2 py-1 rounded text-xs';
      if (kind==='ok') b.classList.add('bg-emerald-500/20','text-emerald-300','ring-1','ring-emerald-500/30');
      else if (kind==='err') b.classList.add('bg-red-500/20','text-red-300','ring-1','ring-red-500/30');
      else b.classList.add('bg-white/10','text-zinc-300','ring-1','ring-white/15');
      b.textContent = (kind==='ok' ? 'OK' : kind==='err' ? 'Error' : 'Scanning');
      t.textContent = msg || '';
    }
    function setStatus(msg, kind='neutral'){ const s=document.getElementById('status'); const d=document.getElementById('status-dot'); s.textContent = msg; d.className='inline-block w-2 h-2 rounded-full'; if(kind==='scan'){ d.classList.add('bg-indigo-400','animate-pulse'); } else if(kind==='ok'){ d.classList.add('bg-emerald-400'); } else if(kind==='err'){ d.classList.add('bg-rose-400'); } else { d.classList.add('bg-zinc-500'); } }
    function getToken(){ return document.querySelector('meta[name="csrf-token"]').getAttribute('content'); }
    const VERIFY_URL = @json(route('tickets.verify'));

    // Modal elements
    const modalEl = document.getElementById('scan-modal');
    const modalCard = document.getElementById('scan-modal-card');
    const modalOverlay = document.getElementById('scan-modal-overlay');
    const modalClose = document.getElementById('scan-modal-close');
    const modalBadge = document.getElementById('scan-modal-badge');
    const modalTitle = document.getElementById('scan-modal-title');
    const modalSub = document.getElementById('scan-modal-sub');
    const modalMeta = document.getElementById('scan-modal-meta');
    let modalTimer = null;

    // Robust scroll lock
    let __scrollY = 0;
    function lockScroll(){
      __scrollY = window.scrollY || document.documentElement.scrollTop || 0;
      document.documentElement.classList.add('scan-modal-open');
      document.documentElement.style.overflow = 'hidden';
      document.documentElement.style.overscrollBehavior = 'none';
      document.body.style.overflow = 'hidden';
      document.body.style.position = 'fixed';
      document.body.style.top = `-${__scrollY}px`;
      document.body.style.width = '100%';
    }
    function unlockScroll(){
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.width = '';
      document.body.style.overflow = '';
      document.documentElement.style.overflow = '';
      document.documentElement.style.overscrollBehavior = '';
      document.documentElement.classList.remove('scan-modal-open');
      window.scrollTo(0, __scrollY || 0);
    }

    function closeScanModal(){ if(modalEl){ modalEl.classList.add('hidden'); } if(modalTimer){ clearTimeout(modalTimer); modalTimer=null; } unlockScroll(); }
    function openScanModal(kind, opts){
      if(!modalEl) return;
      modalBadge.className = 'mx-auto mb-3 inline-flex items-center px-3 py-1.5 rounded-full text-xs';
      if (kind==='ok') modalBadge.classList.add('bg-emerald-500/20','text-emerald-300','ring-1','ring-emerald-500/30');
      else if (kind==='warn') modalBadge.classList.add('bg-yellow-500/20','text-yellow-300','ring-1','ring-yellow-500/30');
      else modalBadge.classList.add('bg-red-500/20','text-red-300','ring-1','ring-red-500/30');
      modalBadge.textContent = kind==='ok' ? 'Valid ticket' : (kind==='warn' ? 'Already checked in' : 'Invalid ticket');
      modalTitle.textContent = opts?.title || '';
      modalSub.textContent = opts?.sub || '';
      modalMeta.textContent = opts?.last ? ('Last check-in: ' + new Date(opts.last).toLocaleString()) : '';
      modalEl.classList.remove('hidden');
      modalCard.classList.remove('animate-bounceIn'); void modalCard.offsetWidth; modalCard.classList.add('animate-bounceIn');
      if (modalTimer) clearTimeout(modalTimer);
      lockScroll();
    }
    // Keep modal open unless Close is clicked
    modalOverlay?.addEventListener('click', (e)=>{ e.preventDefault(); e.stopPropagation(); });
    modalEl?.addEventListener('click', (e)=>{ if(e.target === modalEl) { e.preventDefault(); e.stopPropagation(); } });
    modalClose?.addEventListener('click', closeScanModal);
    // Prevent scroll on overlay for iOS
    const preventScroll = (ev)=>{ ev.preventDefault(); };
    modalEl?.addEventListener('wheel', preventScroll, { passive:false });
    modalEl?.addEventListener('touchmove', preventScroll, { passive:false });
    // Disable ESC-to-close: no handler

    function notify(kind){ try{ if(navigator.vibrate){ if(kind==='ok') navigator.vibrate([30]); else if(kind==='warn') navigator.vibrate([20,40,20]); else navigator.vibrate([30,30,30]); } }catch(_){} }

    async function verifyPayload(text){
      try{
        setBadge('scan','Verifying…');
        const res = await fetch(VERIFY_URL, {
          method: 'POST', credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getToken(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: JSON.stringify({ text })
        });
        const data = await res.json();
        if (!res.ok || !data || !data.ok){ setBadge('err','Invalid or unauthorized.'); openScanModal('err',{ title:'Invalid ticket', sub:'' }); notify('err'); return; }
        const ev = data.event?.title || 'Event';
        const buyer = data.buyer?.name || ''; const email = data.buyer?.email || '';
        const status = data.valid ? 'Valid ticket' : 'Invalid ticket';
        setBadge(data.valid ? 'ok' : 'err', `${status} • ${ev} • ${buyer} (${email})`);
        openScanModal(data.valid ? 'ok' : 'err', { title: status, sub: `${buyer} • ${ev}`, last: data.last_checkin_at });
        notify(data.valid ? 'ok' : 'err');
      }catch(e){ setBadge('err','Network error.'); openScanModal('err',{ title:'Network error', sub:'Please try again' }); }
    }

    // Camera scanning (BarcodeDetector -> jsQR)
    let stream = null, running = false, rafId = null, detector = null;
    const video = document.getElementById('video');

    async function startCamera(){
      try{
        setStatus('Starting…','scan');
        // Try hard for rear camera first
        const attempts = [
          { video: { facingMode: { exact: 'environment' } }, audio: false },
          { video: { facingMode: { ideal: 'environment' } }, audio: false },
        ];
        let lastErr = null;
        for (const c of attempts) {
          try { stream = await navigator.mediaDevices.getUserMedia(c); lastErr = null; break; } catch (e) { lastErr = e; }
        }

        // If still no stream, try to pick a back camera by label
        if (!stream) {
          try {
            const temp = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            temp.getTracks().forEach(t=>t.stop());
            const devices = await navigator.mediaDevices.enumerateDevices();
            const cams = devices.filter(d=>d.kind==='videoinput');
            const back = cams.find(c => /back|rear|environment|outward|world/i.test(c.label||''));
            if (back) {
              stream = await navigator.mediaDevices.getUserMedia({ video: { deviceId: { exact: back.deviceId } }, audio: false });
            }
          } catch (e) { lastErr = e; }
        }

        // Final fallback: any camera
        if (!stream) {
          try { stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false }); } catch (e) { lastErr = e; }
        }

        if (!stream) { throw lastErr || new Error('No camera'); }

        video.muted = true; // ensure autoplay without user gesture
        video.srcObject = stream; await video.play();
        running = true; setStatus('Scanning…','scan');
        if ('BarcodeDetector' in window){ detector = new BarcodeDetector({ formats:['qr_code'] }); scanNative(); }
        else { await ensureJsQR(); scanCanvas(); }
      }catch(e){ setStatus('Blocked','err'); setBadge('err','Allow camera access in your browser (or use image upload).'); }
    }
    function stopCamera(){ try{ running=false; if(rafId) cancelAnimationFrame(rafId); rafId=null; if(video){ video.pause?.(); video.srcObject=null; } if(stream){ stream.getTracks().forEach(t=>t.stop()); stream=null; } }catch{} setStatus('Stopped'); }

    async function ensureJsQR(){ if (window.jsQR) return true; const urls=['https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js','https://unpkg.com/jsqr@1.4.0/dist/jsQR.js']; for (const u of urls){ try{ await loadScript(u); if(window.jsQR) return true; }catch{} } return false; }
    function loadScript(u){ return new Promise((res,rej)=>{ const s=document.createElement('script'); s.src=u; s.async=true; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); }

    async function scanNative(){
      const tick = async () => {
        if (!running) return;
        try{
          const codes = await detector.detect(video);
          if (codes && codes.length){ const txt = codes[0].rawValue; if (txt) { running=false; setStatus('Found','ok'); await verifyPayload(txt); } }
        }catch{}
        rafId = requestAnimationFrame(tick);
      };
      rafId = requestAnimationFrame(tick);
    }

    function scanCanvas(){
      const canvas = document.createElement('canvas'); const ctx = canvas.getContext('2d');
      const W = 480, H = 360; canvas.width=W; canvas.height=H;
      const tick = () => {
        if (!running) return;
        try{
          ctx.drawImage(video, 0, 0, W, H);
          const img = ctx.getImageData(0,0,W,H);
          const qr = window.jsQR(img.data, img.width, img.height);
          if (qr && qr.data){ running=false; setStatus('Found','ok'); verifyPayload(qr.data); return; }
        }catch{}
        rafId = requestAnimationFrame(tick);
      };
      rafId = requestAnimationFrame(tick);
    }

    // Image decode
    async function scanImage(file){
      if (!file){ setBadge('err','Choose an image first.'); return; }
      setBadge('scan','Decoding image…');
      try{
        if ('BarcodeDetector' in window){
          const det = new BarcodeDetector({ formats:['qr_code'] });
          const bmp = await createImageBitmap(file);
          const codes = await det.detect(bmp);
          if (codes && codes.length){ await verifyPayload(codes[0].rawValue); return; }
        }
        await ensureJsQR();
        const img = new Image(); const url = URL.createObjectURL(file);
        await new Promise((res,rej)=>{ img.onload=res; img.onerror=rej; img.src=url; });
        const canvas=document.createElement('canvas'); const ctx=canvas.getContext('2d'); canvas.width=img.width; canvas.height=img.height; ctx.drawImage(img,0,0); const data=ctx.getImageData(0,0,canvas.width,canvas.height);
        const qr = window.jsQR && window.jsQR(data.data, data.width, data.height);
        URL.revokeObjectURL(url);
        if (qr && qr.data){ await verifyPayload(qr.data); return; }
        setBadge('err','Could not find a QR in that image.');
      }catch{ setBadge('err','Failed to decode image.'); }
    }

    // Wire up UI
    document.getElementById('btn-start').addEventListener('click', startCamera);
    document.getElementById('btn-stop').addEventListener('click', stopCamera);
    document.getElementById('btn-verify').addEventListener('click', async ()=>{
      const v = document.getElementById('code').value.trim(); if (v) await verifyPayload(v); else setBadge('err','Enter a code.');
    });

    const fileInput = document.getElementById('file');
    const dropzone = document.getElementById('dropzone');
    dropzone.addEventListener('click', ()=>fileInput.click());
    dropzone.addEventListener('dragover', (e)=>{ e.preventDefault(); dropzone.classList.add('border-indigo-400'); });
    dropzone.addEventListener('dragleave', ()=> dropzone.classList.remove('border-indigo-400'));
    dropzone.addEventListener('drop', async (e)=>{ e.preventDefault(); dropzone.classList.remove('border-indigo-400'); const f = e.dataTransfer.files[0]; if (f) await scanImage(f); });
    fileInput.addEventListener('change', async (e)=>{ const f=e.target.files[0]; if (f) await scanImage(f); });
  </script>
</x-app-layout>
