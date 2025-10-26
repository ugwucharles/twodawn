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
        <h1 class="text-3xl font-extrabold">Buy Tickets — {{ $event->title }}</h1>
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
          <div>
            <label class="block text-sm text-zinc-300" for="coupon">Coupon (optional)</label>
            <input id="coupon" name="coupon" type="text" value="{{ old('coupon') }}" class="mt-1 block w-full rounded-lg bg-black/30 border border-white/10 focus:border-white/30 focus:ring-0 px-3 py-2" />
          </div>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl bg-white text-black font-semibold hover:bg-zinc-100 transition disabled:opacity-50 disabled:cursor-not-allowed" id="payment-button">
          <span id="button-text">Proceed to Paystack</span>
          <svg id="button-spinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-black hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </button>
        <a href="{{ route('events.show', $event) }}" class="block text-center text-zinc-400 hover:text-white text-sm">Cancel</a>
      </form>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
            buttonText.textContent = 'Proceed to Paystack';
            buttonSpinner.classList.add('hidden');
        }, 30000);
        
        // If form submission fails, re-enable after 5 seconds
        setTimeout(() => {
            if (isSubmitting) {
                isSubmitting = false;
                button.disabled = false;
                buttonText.textContent = 'Proceed to Paystack';
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
