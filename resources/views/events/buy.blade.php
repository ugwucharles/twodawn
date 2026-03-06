@extends('layouts.public')

@section('content')
<section class="relative py-12 sm:py-16">
  <div class="max-w-5xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
    <div class="space-y-4">
      <div class="aspect-[16/10] overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        @if($event->image_url)
          <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="h-full w-full object-cover"/>
        @else
          <div class="h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
        @endif
      </div>
      <div>
        @php $isFree = ((float)($event->early_bird_price ?? $event->price ?? 0)) <= 0; @endphp
        <h1 class="text-3xl font-black text-black">{{ $isFree ? 'Get Tickets' : 'Buy Tickets' }}</h1>
        <h2 class="text-xl font-bold text-zinc-900 mt-1">{{ $event->title }}</h2>
        <div class="mt-2 text-zinc-600 font-medium">{{ optional($event->starts_at)->format('D, M j, Y g:i A') }} @if($event->venue) • {{ $event->venue }} @endif</div>
      </div>
    </div>

    <div>
      @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-2xl border border-red-200 shadow-sm">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @php
        $now = now();
        $unitPrice = (float) ($event->price ?? 0);
        $isEarly = false;
        if (!is_null($event->early_bird_price) && !is_null($event->early_bird_ends_at) && $now->lte($event->early_bird_ends_at)) {
          $unitPrice = (float) $event->early_bird_price;
          $isEarly = true;
        }
      @endphp
      @php $unitPriceStr = number_format($unitPrice ?? 0, 2, '.', ''); @endphp

      <form method="POST" action="{{ route('orders.create', $event, false) }}" class="space-y-6 rounded-3xl bg-white border border-zinc-200 p-8 shadow-sm" id="payment-form">
        @csrf
        <!-- Security token to prevent double submissions -->
        <input type="hidden" name="submission_token" value="{{ \Illuminate\Support\Str::random(32) }}" id="submission-token">
        @if(($unitPrice ?? 0) <= 0 && config('services.turnstile.site_key'))
          <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
          <div class="mt-2">
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="dark"></div>
          </div>
        @endif
        <div>
          <label class="block text-xs font-black uppercase tracking-widest text-black" for="buyer_name">Full name</label>
          <input id="buyer_name" name="buyer_name" type="text" required value="{{ old('buyer_name') }}" class="mt-1 block w-full border-0 border-b border-zinc-300 bg-transparent focus:border-black focus:ring-0 px-0 py-2 text-black placeholder:text-zinc-300" placeholder="John Doe" />
        </div>
        <div>
          <label class="block text-xs font-black uppercase tracking-widest text-black" for="buyer_email">Email address</label>
          <input id="buyer_email" name="buyer_email" type="email" required value="{{ old('buyer_email') }}" class="mt-1 block w-full border-0 border-b border-zinc-300 bg-transparent focus:border-black focus:ring-0 px-0 py-2 text-black placeholder:text-zinc-300" placeholder="john@example.com" />
        </div>
        <div>
          <label class="block text-xs font-black uppercase tracking-widest text-black" for="buyer_phone">Phone number (optional)</label>
          <input id="buyer_phone" name="buyer_phone" type="text" value="{{ old('buyer_phone') }}" class="mt-1 block w-full border-0 border-b border-zinc-300 bg-transparent focus:border-black focus:ring-0 px-0 py-2 text-black placeholder:text-zinc-300" placeholder="+234..." />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-black uppercase tracking-widest text-black" for="quantity">Quantity</label>
            <input id="quantity" name="quantity" type="number" min="1" step="1" required value="{{ old('quantity', 1) }}" class="mt-1 block w-full border-0 border-b border-zinc-300 bg-transparent focus:border-black focus:ring-0 px-0 py-2 text-black" />
          </div>
          @if($unitPrice > 0 || (is_array($event->ticket_types) && count($event->ticket_types) > 0))
          <div>
            <label class="block text-xs font-black uppercase tracking-widest text-black" for="coupon">Coupon code</label>
            <input id="coupon" name="coupon" type="text" value="{{ old('coupon') }}" class="mt-1 block w-full border-0 border-b border-zinc-300 bg-transparent focus:border-black focus:ring-0 px-0 py-2 text-black placeholder:text-zinc-300" placeholder="Optional" />
            <div id="coupon-msg" class="mt-1 text-xs"></div>
          </div>
          @endif
        </div>

        @if(is_array($event->ticket_types) && count($event->ticket_types) > 0)
        <div class="space-y-3">
          <label class="block text-xs font-black uppercase tracking-widest text-black">Ticket Type</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="ticket-types-grid">
            @foreach($event->ticket_types as $index => $type)
              <label class="relative flex cursor-pointer rounded-xl border border-zinc-200 bg-white p-4 shadow-sm focus:outline-none ticket-type-label hover:border-black transition">
                <input type="radio" name="ticket_type" value="{{ $type['name'] }}" class="sr-only" required @checked(old('ticket_type') === $type['name'] || $index === 0) data-price="{{ $type['price'] }}">
                <span class="flex flex-1">
                  <span class="flex flex-col">
                    <span class="block text-sm font-bold text-black">{{ $type['name'] }}</span>
                    <span class="mt-1 flex items-center text-sm text-zinc-500">₦{{ number_format($type['price'], 0) }}</span>
                  </span>
                </span>
                <svg class="h-5 w-5 text-indigo-600 hidden check-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </label>
            @endforeach
          </div>
        </div>
        @endif

        <!-- Cost summary -->
        <div class="rounded-2xl bg-zinc-50 border border-zinc-100 p-5 text-sm text-black">
          <div class="flex justify-between font-medium"><span>Price per ticket</span><span>@if($unitPrice <= 0) Free @else ₦{{ number_format($unitPrice, 0) }} @if($isEarly)<span class="text-xs text-emerald-600 ml-1 font-bold">(early-bird)</span>@endif @endif</span></div>
          @if($event->pass_fees_to_buyer && $unitPrice > 0)
          <div class="flex justify-between text-zinc-500 mt-1 italic"><span>Platform fee per ticket</span><span>7% + ₦50</span></div>
          @endif
          <div class="mt-4 space-y-2">
            <div class="flex justify-between"><span>Subtotal</span><span id="sum-subtotal" class="font-bold text-black">₦0</span></div>
            @if($event->pass_fees_to_buyer)
            <div class="flex justify-between"><span>Fees</span><span id="sum-fees" class="font-bold text-black">₦0</span></div>
            @endif
            <div class="pt-3 mt-3 border-t border-zinc-200 flex justify-between font-black text-lg"><span>Total</span><span id="sum-total">₦0</span></div>
          </div>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-4 rounded-full bg-black text-white font-black hover:bg-zinc-800 transition shadow-xl disabled:opacity-50 disabled:cursor-not-allowed uppercase tracking-wider text-sm" id="payment-button">
          <span id="button-text">{{ $unitPrice <= 0 ? 'Get Ticket' : 'Proceed to Paystack' }}</span>
          <svg id="button-spinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </button>
        <a href="{{ $event->public_url }}" class="block text-center text-zinc-500 hover:text-black text-sm font-bold">Cancel</a>
      </form>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const HAS_TICKET_TYPES = @json(is_array($event->ticket_types) && count($event->ticket_types) > 0);
    const BASE_UNIT_PRICE = parseFloat(@json($unitPriceStr));
    const PASS_FEES = @json((bool) $event->pass_fees_to_buyer);
    const QUOTE_URL = @json(route('orders.quote', $event));

    const qtyEl = document.getElementById('quantity');
    const sumSubtotal = document.getElementById('sum-subtotal');
    const sumFees = document.getElementById('sum-fees');
    const sumTotal = document.getElementById('sum-total');
    const typeRadios = document.querySelectorAll('input[name="ticket_type"]');
    
    // UI styling for selected ticket type
    function updateSelectedRadioUI() {
        if (!HAS_TICKET_TYPES) return;
        document.querySelectorAll('.ticket-type-label').forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            const check = label.querySelector('.check-icon');
            if (radio.checked) {
                label.classList.add('border-black', 'ring-1', 'ring-black');
                if (check) check.classList.remove('hidden');
            } else {
                label.classList.remove('border-black', 'ring-1', 'ring-black');
                if (check) check.classList.add('hidden');
            }
        });
    }

    // Get the current active unit price
    function getCurrentUnitPrice() {
        if (HAS_TICKET_TYPES) {
            const checked = document.querySelector('input[name="ticket_type"]:checked');
            return checked ? parseFloat(checked.dataset.price || '0') : 0;
        }
        return BASE_UNIT_PRICE;
    }

    function fmtKoboToNaira(k){ return new Intl.NumberFormat('en-NG').format(Math.round(k/100)); }

    function recalc(){
      const currentPrice = getCurrentUnitPrice();
      const feesOn = (currentPrice > 0) && PASS_FEES;
      const feePerKobo = feesOn ? (Math.round(currentPrice * 0.07 * 100) + 5000) : 0;
      
      let q = parseInt(qtyEl.value || '1', 10); if (!Number.isFinite(q) || q < 1) q = 1;
      const subK = Math.round(currentPrice * 100) * q;
      const feeK = feePerKobo * q;
      const totK = subK + feeK;
      
      if (sumSubtotal) sumSubtotal.textContent = '₦' + fmtKoboToNaira(subK);
      if (sumFees) sumFees.textContent = '₦' + fmtKoboToNaira(feeK);
      if (sumTotal) sumTotal.textContent = '₦' + fmtKoboToNaira(totK);
      
      const btnText = document.getElementById('button-text');
      if (btnText) btnText.textContent = (totK <= 0 ? 'Get Ticket' : ('Pay ₦' + fmtKoboToNaira(totK) + ' via Paystack'));
    }

    function debounce(fn, ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

    async function quote(){
      const currentPrice = getCurrentUnitPrice();
      if (currentPrice <= 0 && !HAS_TICKET_TYPES) return; // no quote for purely free static events
      try{
        const q = parseInt(qtyEl.value||'1',10)||1;
        const c = (document.getElementById('coupon')?.value || '').trim();
        const t = document.querySelector('input[name="ticket_type"]:checked')?.value || '';
        
        let url = QUOTE_URL + '?quantity=' + encodeURIComponent(q);
        if (c) url += '&coupon=' + encodeURIComponent(c);
        if (t) url += '&ticket_type=' + encodeURIComponent(t);
        
        const resp = await fetch(url, { headers: { 'Accept':'application/json' } });
        if (!resp.ok) return; const data = await resp.json();
        if (!data || !data.ok) return;
        if (sumSubtotal) sumSubtotal.textContent = '₦' + fmtKoboToNaira(data.subtotal_kobo||0);
        if (sumFees && typeof data.fees_kobo === 'number') sumFees.textContent = '₦' + fmtKoboToNaira(data.fees_kobo||0);
        if (sumTotal) sumTotal.textContent = '₦' + fmtKoboToNaira(data.total_kobo||0);
        const btnText = document.getElementById('button-text');
        if (btnText) btnText.textContent = (data.total_kobo <= 0 ? 'Get Ticket' : ('Pay ₦' + fmtKoboToNaira(data.total_kobo) + ' via Paystack'));
        const msg = document.getElementById('coupon-msg');
        if (msg){
          if (c && data.coupon_valid){ msg.textContent = 'Coupon applied'; msg.className='mt-1 text-xs text-emerald-300'; }
          else if (c && !data.coupon_valid){ msg.textContent = 'Invalid or expired coupon'; msg.className='mt-1 text-xs text-red-300'; }
          else { msg.textContent=''; msg.className='mt-1 text-xs'; }
        }
      }catch(_){ /* ignore */ }
    }

    const onChange = debounce(()=>{ recalc(); quote(); }, 250);
    qtyEl.addEventListener('input', onChange);
    const couponEl = document.getElementById('coupon');
    if (couponEl) couponEl.addEventListener('input', onChange);
    
    // Add listeners to ticket type radios
    typeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            updateSelectedRadioUI();
            recalc();
            quote();
        });
    });

    updateSelectedRadioUI();
    recalc();
    quote();
    const form = document.getElementById('payment-form');
    const button = document.getElementById('payment-button');
    const buttonText = document.getElementById('button-text');
    const buttonSpinner = document.getElementById('button-spinner');
    const submissionToken = document.getElementById('submission-token');
    
    // Track if form has been submitted
    let isSubmitting = false;
    let usedTokens = new Set();
    
    // Load used tokens from sessionStorage to prevent duplicate submissions
    const storedTokens = sessionStorage.getItem('used_payment_tokens');
    if (storedTokens) {
        usedTokens = new Set(JSON.parse(storedTokens));
    }
    
    form.addEventListener('submit', function(e) {
        const currentToken = submissionToken.value;
        
        // Fire GA begin_checkout (if GA is present)
        try {
            const qtyEl = document.getElementById('quantity');
            const qty = parseInt((qtyEl && qtyEl.value) || '1', 10) || 1;
            if (window.gtag) {
                window.gtag('event', 'begin_checkout', {
                    currency: 'NGN',
                    items: [{
                        item_id: 'event_{{ $event->id }}',
                        item_name: @json($event->title),
                        item_category: 'Event',
                        quantity: qty,
                    }],
                });
            }
        } catch (_) {}

        // Prevent double submission
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        
        // Check if token was already used
        if (usedTokens.has(currentToken)) {
            e.preventDefault();
            alert('This form has already been submitted. Please refresh the page to try again.');
            return false;
        }
        
        // Mark as submitting
        isSubmitting = true;
        
        // Disable button and show loading state
        button.disabled = true;
        buttonText.textContent = 'Processing...';
        buttonSpinner.classList.remove('hidden');
        
        // Add token to used set
        usedTokens.add(currentToken);
        sessionStorage.setItem('used_payment_tokens', JSON.stringify([...usedTokens]));
        
        // Prevent further form submissions for 30 seconds
        setTimeout(() => {
            isSubmitting = false;
            button.disabled = false;
            recalc();
            buttonSpinner.classList.add('hidden');
        }, 30000);
        
        // If form submission fails, re-enable after 5 seconds
        setTimeout(() => {
            if (isSubmitting) {
                isSubmitting = false;
                button.disabled = false;
                recalc();
                buttonSpinner.classList.add('hidden');
            }
        }, 5000);
    });
    
    // Prevent multiple rapid clicks
    let lastClickTime = 0;
    button.addEventListener('click', function(e) {
        const now = Date.now();
        if (now - lastClickTime < 1000) { // 1 second cooldown
            e.preventDefault();
            return false;
        }
        lastClickTime = now;
    });
    
    // Clear old tokens (older than 1 hour) to prevent storage bloat
    const oneHourAgo = Date.now() - (60 * 60 * 1000);
    const filteredTokens = [...usedTokens].filter(token => {
        // Simple timestamp check - tokens are 32 chars, we'll use a simple approach
        return true; // Keep all for now, could implement timestamp-based filtering
    });
    
    if (filteredTokens.length !== usedTokens.size) {
        usedTokens = new Set(filteredTokens);
        sessionStorage.setItem('used_payment_tokens', JSON.stringify([...usedTokens]));
    }
});
</script>
@endsection
