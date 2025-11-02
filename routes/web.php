<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\EventPublicController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HostRequestController as AdminHostRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HostRequestController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use App\Http\Controllers\Admin\PaystackHealthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ChatController;

Route::get('/', [EventPublicController::class, 'landing'])->name('home');

Route::view('/about', 'about')->name('about');
Route::view('/pricing', 'pricing')->name('pricing');

Route::redirect('/dashboard', '/')->name('dashboard');

// Pricing page
Route::view('/pricing', 'pricing')->name('pricing');

// Admin portal smart redirect (works for guest or authenticated)
Route::get('/admin', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user && ($user->is_admin ?? false)) {
            return redirect()->route('admin.dashboard');
        }
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login')->withErrors(['email' => 'This account does not have admin access.']);
    }
    return redirect()->route('admin.login');
})->name('admin.portal');

// Dedicated admin login routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public event routes
Route::get('/events', [EventPublicController::class, 'index'])->name('events.index');

// Native chat (public)
Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start')->middleware('throttle:6,1');
Route::patch('/chat/{token}', [ChatController::class, 'update'])->name('chat.update')->middleware('throttle:6,1');
Route::get('/chat/{token}/messages', [ChatController::class, 'messages'])->name('chat.messages');
Route::post('/chat/{token}/messages', [ChatController::class, 'postMessage'])->name('chat.messages.post')->middleware('throttle:12,1');
Route::get('/discover', [EventPublicController::class, 'index'])->name('events.discover');
// Important: place the more specific/static routes before the dynamic {event} route
Route::get('/events/recent', [EventPublicController::class, 'recent'])->name('events.recent');
Route::get('/events/{event}/remaining', [EventPublicController::class, 'remaining'])->name('events.remaining');
// Pricing/quote preview (JSON)
Route::get('/events/{event}/quote', [CheckoutController::class, 'quote'])->name('orders.quote');
Route::get('/events/{event}/ics', [EventPublicController::class, 'ics'])->name('events.ics');
// Custom slug route for public event page
Route::get('/event/{slug}', [EventPublicController::class, 'showBySlug'])->name('events.slug');
Route::get('/events/{event}', [EventPublicController::class, 'show'])->name('events.show');

// Comments (guest) with enhanced rate limiting
Route::post('/events/{event}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:3,1') // Reduced from 5 to 3 per minute
    ->name('events.comments.store');

// Guest checkout with enhanced rate limiting
Route::get('/events/{event}/buy', [CheckoutController::class, 'buy'])->name('events.buy');
Route::post('/events/{event}/orders', [CheckoutController::class, 'create'])
    ->middleware(['throttle:3,1']) // Only 3 attempts per minute
    ->name('orders.create');
Route::get('/paystack/callback', [CheckoutController::class, 'callback'])->name('paystack.callback');
// Paystack Webhook (server-to-server). Configure in Paystack Dashboard.
Route::post('/paystack/webhook', App\Http\Controllers\PaystackWebhookController::class)->name('paystack.webhook');
Route::get('/orders/{reference}', [CheckoutController::class, 'showByReference'])->name('orders.public');
Route::get('/orders/{reference}/download', [CheckoutController::class, 'downloadPdf'])->middleware('signed')->name('orders.pdf');


// Host with us (contact) form
Route::post('/host-request', [HostRequestController::class, 'store'])->name('host.request.store');

// Admin routes with enhanced security
Route::middleware(['auth', 'admin', 'throttle:60,1'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events
    Route::resource('events', AdminEventController::class);

    // Backups (list/download/delete)
    Route::get('backups', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
    Route::get('backups/{file}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
    Route::delete('backups/{file}', [App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backups.destroy');
    Route::patch('events/{event}/toggle', [AdminEventController::class, 'togglePublish'])->name('events.toggle');
    Route::patch('events/{event}/toggle-json', [AdminEventController::class, 'togglePublishJson'])->name('events.toggle.json');

    // Host panel links
Route::post('events/{event}/host-tokens', [App\Http\Controllers\Admin\HostTokenController::class, 'store'])->name('events.tokens.store');
Route::patch('host-tokens/{hostToken}/toggle', [App\Http\Controllers\Admin\HostTokenController::class, 'toggle'])->name('tokens.toggle');
Route::delete('host-tokens/{hostToken}', [App\Http\Controllers\Admin\HostTokenController::class, 'destroy'])->name('tokens.destroy');
    
    // Orders admin
    Route::get('orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
Route::get('orders/export-summary', [AdminOrderController::class, 'exportSummary'])->name('orders.export.summary');
Route::get('orders/export-summary-daily', [AdminOrderController::class, 'exportSummaryDaily'])->name('orders.export.summaryDaily');
    Route::resource('orders', AdminOrderController::class)->only(['index','show']);
Route::post('orders/{order}/refunds', [App\Http\Controllers\Admin\RefundController::class, 'store'])->name('orders.refunds.store');
Route::post('orders/{order}/refunds.json', [App\Http\Controllers\Admin\RefundController::class, 'store'])->name('orders.refunds.store.json');

    // Check-ins export
Route::get('checkins/export', [App\Http\Controllers\Admin\TicketScanController::class, 'export'])->name('checkins.export');

    // Ticket scanner
Route::get('scanner', [App\Http\Controllers\Admin\TicketScanController::class, 'index'])->name('scanner.index');
Route::post('scanner/redeem', [App\Http\Controllers\Admin\TicketScanController::class, 'redeem'])->name('scanner.redeem');

    // On-demand backups (admin)
Route::post('ops/backup', function(){ try{ App\Services\BackupService::run(false); return back()->with('status','Backup created'); }catch(\Throwable $e){ return back()->withErrors(['backup'=>$e->getMessage()]); } })->name('ops.backup');

    // Admin chat
Route::get('chat', [App\Http\Controllers\Admin\ChatAdminController::class, 'index'])->name('chat.index');
Route::get('chat/{conversation}', [App\Http\Controllers\Admin\ChatAdminController::class, 'show'])->name('chat.show');
Route::get('chat/{conversation}/messages', [App\Http\Controllers\Admin\ChatAdminController::class, 'messages'])->name('chat.messages');
Route::post('chat/{conversation}/reply', [App\Http\Controllers\Admin\ChatAdminController::class, 'reply'])->name('chat.reply');
Route::post('chat/{conversation}/close', [App\Http\Controllers\Admin\ChatAdminController::class, 'close'])->name('chat.close');
Route::post('chat/{conversation}/reopen', [App\Http\Controllers\Admin\ChatAdminController::class, 'reopen'])->name('chat.reopen');

    // Admin assets proxy (bypass CSP/CDN blocks)
Route::get('assets/html5-qrcode.js', [App\Http\Controllers\Admin\AssetsController::class, 'html5qrcode'])->name('assets.h5qrcode');

    // Tenants (multi-tenant/white-label)
Route::resource('tenants', App\Http\Controllers\Admin\TenantController::class)->except(['show']);

    // Host requests
    Route::resource('host-requests', AdminHostRequestController::class)->only(['index','show','update']);

    // Comments moderation
    Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::patch('comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
    Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
});

// Ticket verification endpoint (admin-only)
Route::post('/verify-ticket', [App\Http\Controllers\Admin\TicketScanController::class, 'verify'])
    ->middleware(['auth','admin'])
    ->name('tickets.verify');

// Host Panel: public, token-scoped
Route::get('/h/assets/html5-qrcode.js', [App\Http\Controllers\Admin\AssetsController::class, 'html5qrcode'])->name('host.assets.h5qrcode');
Route::get('/h/{token}', [App\Http\Controllers\HostPanelController::class, 'show'])->name('host.panel');
Route::get('/h/{token}/people', [App\Http\Controllers\HostPanelController::class, 'people'])->name('host.people');
Route::get('/h/{token}/scan', [App\Http\Controllers\HostPanelController::class, 'scan'])->name('host.scan');
Route::get('/h/{token}/checkins.csv', [App\Http\Controllers\HostPanelController::class, 'exportCheckins'])->name('host.people.export');
Route::get('/h/{token}/sales.csv', [App\Http\Controllers\HostPanelController::class, 'exportSales'])->name('host.sales.export');
Route::get('/h/{token}/sales_daily.csv', [App\Http\Controllers\HostPanelController::class, 'exportSalesDaily'])->name('host.sales.exportDaily');
Route::post('/h/{token}/verify', [App\Http\Controllers\HostPanelController::class, 'verify'])
    ->middleware('throttle:10,1')
->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, App\Http\Middleware\VerifyCsrfToken::class]);

// General health endpoint for uptime monitoring
Route::get('/health', function(){ return response()->json(['ok'=>true,'time'=>now()->toIso8601String()], 200); });

// Sitemap
Route::get('/sitemap.xml', function () {
    try {
        $base = rtrim((string) request()->getSchemeAndHttpHost(), '/');
        $urls = [];
        $push = function ($loc, $lastmod = null, $changefreq = 'weekly', $priority = '0.7') use (&$urls) {
            $urls[] = [
                'loc' => $loc,
                'lastmod' => $lastmod,
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        };

        // Static pages
        $push($base . '/', now()->toAtomString(), 'daily', '1.0');
        $push(url('/events'), now()->toAtomString(), 'daily', '0.8');
        $push(url('/events/recent'), now()->toAtomString(), 'daily', '0.6');
        if (view()->exists('about')) { $push(url('/about'), now()->toAtomString(), 'monthly', '0.4'); }

        // Dynamic event pages (best-effort)
        try {
                    Event::where('is_published', true)->orderByDesc('updated_at')->limit(1000)->get()
                ->each(function ($event) use (&$push) {
                    $push($event->public_url, optional($event->updated_at)->toAtomString(), 'weekly', '0.8');
                });
} catch (Throwable $e) { /* ignore */ }

        $xml = view('sitemap.xml', ['urls' => $urls])->render();
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
} catch (Throwable $e) {
        // Absolute last-resort minimal sitemap (never 500)
        $base = rtrim((string) request()->getSchemeAndHttpHost(), '/');
        $fallback = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"><url><loc>{$base}/</loc><changefreq>daily</changefreq><priority>1.0</priority></url></urlset>";
        return response($fallback, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
})->name('sitemap');

// Temporary Paystack health endpoint (no secrets exposed)
Route::get('/paystack/health', PaystackHealthController::class)->name('paystack.health');

// Public API v1 (read-only)
Route::prefix('api/v1')->middleware('throttle:60,1')->group(function(){
Route::get('/events', [App\Http\Controllers\Api\PublicApiController::class, 'events']);
});

require __DIR__.'/auth.php';
