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

Route::get('/', [EventPublicController::class, 'landing'])->name('home');

Route::view('/about', 'about')->name('about');

Route::redirect('/dashboard', '/')->name('dashboard');

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
// Important: place the more specific/static routes before the dynamic {event} route
Route::get('/events/recent', [EventPublicController::class, 'recent'])->name('events.recent');
Route::get('/events/{event}/remaining', [EventPublicController::class, 'remaining'])->name('events.remaining');
Route::get('/events/{event}', [EventPublicController::class, 'show'])->name('events.show');

// Comments (guest)
Route::post('/events/{event}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('events.comments.store');

// Guest checkout
use App\Http\Controllers\CheckoutController;
Route::get('/events/{event}/buy', [CheckoutController::class, 'buy'])->name('events.buy');
Route::post('/events/{event}/orders', [CheckoutController::class, 'create'])->name('orders.create');
Route::get('/paystack/callback', [CheckoutController::class, 'callback'])->name('paystack.callback');
Route::get('/orders/{reference}', [CheckoutController::class, 'showByReference'])->name('orders.public');
Route::get('/orders/{reference}/download', [CheckoutController::class, 'downloadPdf'])->name('orders.pdf');


// Host with us (contact) form
Route::post('/host-request', [HostRequestController::class, 'store'])->name('host.request.store');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Events
    Route::resource('events', AdminEventController::class);
    Route::patch('events/{event}/toggle', [AdminEventController::class, 'togglePublish'])->name('events.toggle');
    Route::patch('events/{event}/toggle-json', [AdminEventController::class, 'togglePublishJson'])->name('events.toggle.json');
    
    // Orders admin
    Route::get('orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
    Route::resource('orders', AdminOrderController::class)->only(['index','show']);

    // Ticket scanner
    Route::get('scanner', [\App\Http\Controllers\Admin\TicketScanController::class, 'index'])->name('scanner.index');
    Route::post('scanner/redeem', [\App\Http\Controllers\Admin\TicketScanController::class, 'redeem'])->name('scanner.redeem');

    // Host requests
    Route::resource('host-requests', AdminHostRequestController::class)->only(['index','show','update']);

    // Comments moderation
    Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::patch('comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
    Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
});

require __DIR__.'/auth.php';
