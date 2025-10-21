<x-app-layout>
  <div class="py-6">
    <div class="max-w-5xl mx-auto px-6">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">QR Scanner</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-zinc-300 hover:text-white">Dashboard →</a>
      </div>

      <div class="grid lg:grid-cols-2 gap-6 items-start">
        <!-- Live scanner -->
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
          <div class="flex items-center justify-between mb-3">
            <div class="text-sm text-zinc-300">Camera scan</div>
            <div class="text-xs text-zinc-400">Auto-start</div>
          </div>
          <div id="qr-reader" class="rounded-xl overflow-hidden bg-black min-h-[320px] relative">
            <div id="scan-error" class="absolute inset-0 hidden items-center justify-center text-center text-sm text-red-300"></div>
          </div>
          <div class="mt-3 text-xs text-zinc-400">Grant camera permission. On desktop, prefer a USB/HD cam; on mobile, use the rear camera.</div>
          <div class="mt-3 flex items-center gap-2 text-xs">
            <label for="camera-select" class="text-zinc-300">Camera:</label>
            <select id="camera-select" class="rounded bg-black/30 border border-white/10 px-2 py-1 text-zinc-200 min-w-[10rem]"></select>
          </div>
        </div>

        <!-- Manual entry -->
        <div class="rounded-2xl bg-white/5 ring-1 ring-white/10 p-4">
          <div class="text-sm text-zinc-300 mb-2">Enter code manually</div>
          <form id="manual-form" class="flex gap-2" onsubmit="return false;">
            <input id="manual-code" type="text" placeholder="T-XXXXXXXXXX" class="flex-1 rounded-md bg-black/30 border border-white/10 px-3 py-2 focus:border-white/30 focus:ring-0" />
            <button id="manual-submit" class="rounded-md px-4 py-2 bg-white text-black text-sm hover:bg-zinc-100">Redeem</button>
          </form>

          <div id="result" class="mt-4 hidden">
            <div id="status-badge" class="inline-flex items-center px-2 py-1 rounded text-xs"></div>
            <div class="mt-2 text-sm" id="result-text"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Robust loader for html5-qrcode with CDN fallbacks
    let h5qReady;
    function loadScript(url){
      return new Promise((res, rej) => { const s=document.createElement('script'); s.src=url; s.async=true; s.crossOrigin='anonymous'; s.onload=res; s.onerror=rej; document.head.appendChild(s); });
    }
    async function ensureHtml5Qrcode(){
      if (window.Html5Qrcode) return true;
      if (h5qReady) return h5qReady;
      const urls = [
        'https://unpkg.com/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
        'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.10/minified/html5-qrcode.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.10/html5-qrcode.min.js'
      ];
      h5qReady = (async () => {
        for (const u of urls) { try { await loadScript(u); if (window.Html5Qrcode) return true; } catch(_){} }
        throw new Error('Html5Qrcode failed to load');
      })();
      return h5qReady;
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const redeemUrl = @json(route('admin.scanner.redeem'));

    const resultEl = document.getElementById('result');
    const statusBadge = document.getElementById('status-badge');
    const resultText = document.getElementById('result-text');
    const errBox = document.getElementById('scan-error');

    function showError(msg){
      if (!errBox) return; errBox.textContent = msg; errBox.classList.remove('hidden'); errBox.classList.add('flex');
    }
    function clearError(){ if (!errBox) return; errBox.classList.add('hidden'); errBox.classList.remove('flex'); errBox.textContent=''; }

    function showResult(kind, text){
      resultEl.classList.remove('hidden');
      statusBadge.className = 'inline-flex items-center px-2 py-1 rounded text-xs';
      if (kind === 'ok') statusBadge.classList.add('bg-emerald-500/20','text-emerald-300','ring-1','ring-emerald-500/30');
      else if (kind === 'warn') statusBadge.classList.add('bg-yellow-500/20','text-yellow-300','ring-1','ring-yellow-500/30');
      else statusBadge.classList.add('bg-red-500/20','text-red-300','ring-1','ring-red-500/30');
      statusBadge.textContent = (kind==='ok' ? 'OK' : (kind==='warn' ? 'Already used' : 'Not found'));
      resultText.textContent = text;
    }

    async function redeem(code){
      try {
        const res = await fetch(redeemUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
          body: JSON.stringify({ code })
        });
        const data = await res.json();
        if (!res.ok || !data.ok) { showResult('err', 'Not found'); return; }
        const ev = data.event?.title || 'Event';
        if (data.kind === 'order') {
          const buyer = data.buyer?.name || ''; const email = data.buyer?.email || '';
          showResult('ok', `${ev} • ${buyer} (${email}) • Ref ${data.reference || ''}`);
        } else {
          if (data.status === 'already_redeemed') showResult('warn', `${data.code} already used • ${ev}`);
          else showResult('ok', `${data.code} redeemed • ${ev}`);
        }
      } catch (e) {
        showResult('err', 'Network error');
      }
    }

    // Manual form
    document.getElementById('manual-submit').addEventListener('click', () => {
      const v = document.getElementById('manual-code').value.trim();
      if (v) redeem(v);
    });

    // Camera scanner
    let scanner = null; let running = false; let lastText = '';
    const camSelect = document.getElementById('camera-select');

    async function listCamerasHtml5(){
      const cams = await Html5Qrcode.getCameras();
      camSelect.innerHTML = '';
      if (!cams.length) { camSelect.innerHTML = '<option value="">No camera found</option>'; return; }
      for (const c of cams) {
        const opt = document.createElement('option'); opt.value = c.id; opt.textContent = c.label || 'Camera'; camSelect.appendChild(opt);
      }
    }

    async function listCamerasNative(){
      try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cams = devices.filter(d => d.kind === 'videoinput');
        camSelect.innerHTML = '';
        if (!cams.length) { camSelect.innerHTML = '<option value="">No camera found</option>'; return; }
        for (const c of cams) {
          const opt = document.createElement('option'); opt.value = c.deviceId; opt.textContent = c.label || 'Camera'; camSelect.appendChild(opt);
        }
      } catch(e) { camSelect.innerHTML = '<option value="">Select camera</option>'; }
    }

    async function preflight() {
      try {
        const s = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        s.getTracks().forEach(t => t.stop());
      } catch (e) { /* ignore; html5-qrcode will handle */ }
    }

    // Native BarcodeDetector fallback
    let nativeStream = null; let nativeDetector = null; let rafId = null; let videoEl = null;
    async function startNative(){
      try {
        if (!('BarcodeDetector' in window)) throw new Error('BarcodeDetector not supported');
        const supported = (typeof BarcodeDetector.getSupportedFormats === 'function') ? await BarcodeDetector.getSupportedFormats() : ['qr_code'];
        if (supported && !supported.includes('qr_code')) throw new Error('QR format not supported');
        await listCamerasNative();
        const deviceId = camSelect.value || undefined;
        const constraints = deviceId ? { video: { deviceId: { exact: deviceId } } } : { video: { facingMode: 'environment' } };
        nativeStream = await navigator.mediaDevices.getUserMedia(constraints);
        // Create video element
        const host = document.getElementById('qr-reader');
        host.innerHTML = '<video id="qr-video" playsinline style="width:100%;height:auto;object-fit:cover"></video>';
        videoEl = document.getElementById('qr-video');
        videoEl.srcObject = nativeStream; await videoEl.play();
        nativeDetector = new BarcodeDetector({ formats: ['qr_code'] });
        const tick = async () => {
          if (!videoEl || videoEl.readyState < 2) { rafId = requestAnimationFrame(tick); return; }
          try {
            const codes = await nativeDetector.detect(videoEl);
            if (codes && codes.length) {
              const text = codes[0].rawValue; if (text && text !== lastText) { lastText = text; redeem(text); setTimeout(()=>{ lastText=''; }, 1200); }
            }
          } catch(_){}
          rafId = requestAnimationFrame(tick);
        };
        rafId = requestAnimationFrame(tick);
        running = true;
      } catch (err) {
        throw err;
      }
    }

    async function stopNative(){
      try { if (rafId) cancelAnimationFrame(rafId); rafId = null; if (videoEl) { videoEl.pause?.(); videoEl.srcObject = null; videoEl = null; } if (nativeStream) { nativeStream.getTracks().forEach(t=>t.stop()); nativeStream=null; } } catch(_){}
    }

    async function start(){
      if (running) return;
      clearError();
      try {
        await preflight();
        // Try html5-qrcode first
        await ensureHtml5Qrcode();
        if (!scanner) scanner = new Html5Qrcode('qr-reader');
        await listCamerasHtml5();
        let camId = camSelect.value || null;
        if (!camId) {
          try {
            const cams = await Html5Qrcode.getCameras();
            camId = cams.find(c => /back|rear|environment/i.test(c.label))?.id || (cams[0]?.id) || null;
          } catch(e) { /* ignore */ }
        }
        const config = { fps: 12, qrbox: { width: 260, height: 260 }, rememberLastUsedCamera: true, aspectRatio: 1.7778 };
        const onSuccess = (decodedText) => { if (!decodedText || decodedText === lastText) return; lastText = decodedText; redeem(decodedText); setTimeout(() => { lastText=''; }, 1200); };
        const onErr = (_) => {};
        if (camId) { await scanner.start(camId, config, onSuccess, onErr); }
        else { await scanner.start({ facingMode: { exact: 'environment' } }, config, onSuccess, onErr).catch(() => scanner.start({ facingMode: 'environment' }, config, onSuccess, onErr)); }
        running = true;
      } catch (e1) {
        // Fallback to native detector
        try { await startNative(); }
        catch(e2){ showError('Cannot access camera (' + (e2 && e2.message ? e2.message : 'unknown') + ').'); }
      }
    }
    async function stop(){
      try { if (scanner && running) { await scanner.stop(); await scanner.clear(); } } catch (e) { /* ignore */ }
      await stopNative();
      running = false;
    }

    document.addEventListener('DOMContentLoaded', () => { start(); });
    camSelect?.addEventListener('change', async () => { await stop(); start(); });
    window.addEventListener('visibilitychange', async () => { if (document.hidden) { await stop(); } else { start(); } });
  </script>
</x-app-layout>