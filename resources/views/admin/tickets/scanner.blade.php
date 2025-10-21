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
            <button id="btn-toggle" class="text-xs px-2 py-1 rounded bg-white/10 ring-1 ring-white/10 hover:bg-white/20">Start</button>
          </div>
          <div id="qr-reader" class="rounded-xl overflow-hidden bg-black min-h-[320px]"></div>
          <div class="mt-3 text-xs text-zinc-400">Grant camera permission. On desktop, prefer a USB/HD cam; on mobile, use the rear camera.</div>
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

  <script src="https://unpkg.com/html5-qrcode@2.3.10/minified/html5-qrcode.min.js"></script>
  <script>
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const redeemUrl = @json(route('admin.scanner.redeem'));

    const resultEl = document.getElementById('result');
    const statusBadge = document.getElementById('status-badge');
    const resultText = document.getElementById('result-text');

    function showResult(kind, text){
      resultEl.classList.remove('hidden');
      statusBadge.className = 'inline-flex items-center px-2 py-1 rounded text-xs';
      if (kind === 'ok') statusBadge.classList.add('bg-emerald-500/20','text-emerald-300','ring-1','ring-emerald-500/30');
      else if (kind === 'warn') statusBadge.classList.add('bg-yellow-500/20','text-yellow-300','ring-1','ring-yellow-500/30');
      else statusBadge.classList.add('bg-red-500/20','text-red-300','ring-1','ring-red-500/30');
      statusBadge.textContent = (kind==='ok' ? 'Redeemed' : (kind==='warn' ? 'Already used' : 'Not found'));
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
        if (!res.ok || !data.ok) {
          showResult('err', 'Ticket not found');
          return;
        }
        const ev = data.event?.title || 'Event';
        if (data.status === 'already_redeemed') {
          showResult('warn', `${data.code} was already redeemed • ${ev}`);
        } else {
          showResult('ok', `${data.code} redeemed • ${ev}`);
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
    let scanner = null; let running = false;
    const btn = document.getElementById('btn-toggle');
    async function start(){
      if (running) return;
      if (!scanner) scanner = new Html5Qrcode('qr-reader');
      const cameras = await Html5Qrcode.getCameras();
      const camId = cameras.find(c => /back|rear|environment/i.test(c.label))?.id || (cameras[0]?.id);
      await scanner.start(
        camId,
        { fps: 12, qrbox: { width: 240, height: 240 } },
        (decodedText) => {
          // Debounce quick duplicates
          if (!decodedText) return;
          redeem(decodedText);
        },
        (err) => {}
      );
      running = true; btn.textContent = 'Stop';
    }
    async function stop(){ if (!scanner || !running) return; await scanner.stop(); running = false; btn.textContent = 'Start'; }
    btn.addEventListener('click', () => running ? stop() : start());
  </script>
</x-app-layout>