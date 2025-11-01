@extends('layouts.public')

@section('content')
<section class="relative py-12 sm:py-16">
  <div class="max-w-5xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-10">
    <div class="space-y-4">
      <div class="aspect-[16/10] overflow-hidden rounded-2xl ring-1 ring-white/10 bg-white/5">
        @if($event->image_url)
          <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="h-full w-full object-cover"/>
        @else
          <div class="h-full w-full bg-gradient-to-br from-indigo-500 via-fuchsia-500 to-rose-500"></div>
        @endif
      </div>
      <div>
        @php $isFree = ((float)($event->early_bird_price ?? $event->price ?? 0)) <= 0; @endphp
        <h1 class="text-3xl font-extrabold">{{ $isFree ? 'Get Tickets' : 'Buy Tickets' }} — {{ $event->title }}</h1>
        <div class="mt-2 text-zinc-300">{{ optional($event->starts_at)->format('D, M j, Y g:i A') }} @if($event->venue) • {{ $event->venue }} @endif</div>
      </div>
    </div>

    <div>
      @if ($errors->any())
        <div class="mb-4 p-3 bg-red-500/10 text-red-300 rounded ring-1 ring-red-500/30">
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

      <form method="POST" action="{{ route('orders.create', $event, false) }}" class="space-y-4 rounded-2xl bg-white/5 ring-1 ring-white/10 p-6" id="payment-form">
        @csrf
        <!-- Security token to prevent double submissions -->
        <input type="hidden" name="submission_token" value="{{ Str::random(32) }}" id="submission-token">
        <div>
          <label class="block text-sm text-zinc-300" for="buyer_name">Full name</label>
          <input id="buyer_name" name="buyer_name" type="text" required value="{{ old('buyer_name') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-zinc-300" for="buyer_email">Email</label>
          <input id="buyer_email" name="buyer_email" type="email" required value="{{ old('buyer_email') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-zinc-300" for="buyer_phone">Phone (optional)</label>
          <input id="buyer_phone" name="buyer_phone" type="text" value="{{ old('buyer_phone') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-zinc-300" for="quantity">Quantity</label>
            <input id="quantity" name="quantity" type="number" min="1" step="1" required value="{{ old('quantity', 1) }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
          </div>
          @if($unitPrice > 0)
          <div>
            <label class="block text-sm text-zinc-300" for="coupon">Coupon (optional)</label>
            <input id="coupon" name="coupon" type="text" value="{{ old('coupon') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
            <div id="coupon-msg" class="mt-1 text-xs"></div>
          </div>
          @endif
        </div>

        <!-- Cost summary -->
        <div class="rounded-lg bg-black/20 border border-white/10 p-4 text-sm">
          <div class="flex justify-between"><span>Price</span><span>@if($unitPrice <= 0) Free @else ₦{{ number_format($unitPrice, 0) }} @if($isEarly)<span class="text-xs text-emerald-300 ml-1">(early-bird)</span>@endif @endif</span></div>
          @if($event->pass_fees_to_buyer && $unitPrice > 0)
          <div class="flex justify-between text-zinc-300"><span>Platform fee per ticket</span><span>5% + ₦50</span></div>
          @endif
          <div class="mt-3 space-y-1">
            <div class="flex justify-between"><span>Subtotal</span><span id="sum-subtotal">₦0</span></div>
            @if($event->pass_fees_to_buyer)
            <div class="flex justify-between"><span>Fees</span><span id="sum-fees">₦0</span></div>
            @endif
            <div class="pt-1 mt-1 border-t border-white/10 flex justify-between font-semibold"><span>Total</span><span id="sum-total">₦0</span></div>
          </div>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-black font-semibold hover:bg-zinc-100 transition disabled:opacity-50 disabled:cursor-not-allowed" id="payment-button">
          <span id="button-text">{{ $unitPrice <= 0 ? 'Get Ticket' : 'Proceed to Paystack' }}</span>
          <svg id="button-spinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-black hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </button>
        <a href="{{ $event->public_url }}" class="block text-center text-zinc-400 hover:text-white text-sm">Cancel</a>
      </form>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const UNIT_PRICE = parseFloat(@json(number_format($unitPrice, 2, '.', '')));
    const FEES_ON = (UNIT_PRICE > 0) && @json((bool) $event->pass_fees_to_buyer);
    const FEE_PER_TICKET_K = FEES_ON ? (Math.round(UNIT_PRICE * 0.05 * 100) + 5000) : 0; // 5% + ₦50
    const QUOTE_URL = UNIT_PRICE > 0 ? @json(route('orders.quote', $event)) : null;

    const qtyEl = document.getElementById('quantity');
    const sumSubtotal = document.getElementById('sum-subtotal');
    const sumFees = document.getElementById('sum-fees');
    const sumTotal = document.getElementById('sum-total');

    function fmtKoboToNaira(k){ return new Intl.NumberFormat('en-NG').format(Math.round(k/100)); }

    function recalc(){
      let q = parseInt(qtyEl.value || '1', 10); if (!Number.isFinite(q) || q < 1) q = 1;
      const subK = Math.round(UNIT_PRICE * 100) * q;
      const feeK = FEE_PER_TICKET_K * q;
      const totK = subK + feeK;
      if (sumSubtotal) sumSubtotal.textContent = '₦' + fmtKoboToNaira(subK);
      if (sumFees) sumFees.textContent = '₦' + fmtKoboToNaira(feeK);
      if (sumTotal) sumTotal.textContent = '₦' + fmtKoboToNaira(totK);
      const btnText = document.getElementById('button-text');
      if (btnText) btnText.textContent = (totK <= 0 ? 'Get Ticket' : ('Pay ₦' + fmtKoboToNaira(totK) + ' via Paystack'));
    }

    function debounce(fn, ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

    async function quote(){
      if (!QUOTE_URL) return; // no quote for free events
      try{
        const q = parseInt(qtyEl.value||'1',10)||1;
        const c = (document.getElementById('coupon')?.value || '').trim();
        const resp = await fetch(QUOTE_URL + '?quantity=' + encodeURIComponent(q) + (c?('&coupon='+encodeURIComponent(c)):'') , { headers: { 'Accept':'application/json' } });
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
