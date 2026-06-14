<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->getWallet();
        $withdrawals = $wallet->withdrawals()->latest()->paginate(10);
        
        // Calculate available balance (only from ended events)
        $events = $user->events()->where('ends_at', '<', now())->get();
        $eventIds = $events->pluck('id')->toArray();
        
        $availableForWithdrawal = 0;
        if ($events->count() > 0) {
            $totalRevenue = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->sum('amount');
            $twoDawnFee = 0;
            $orders = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->get();
            foreach ($orders as $order) {
                $fee = ($order->amount * 0.05) + 5000; // 5% + ₦50 (in kobo)
                $twoDawnFee += $fee;
            }
            $availableForWithdrawal = max(0, ($totalRevenue - $twoDawnFee) / 100);
        }
        
        return view('organizer.wallet.index', compact('wallet', 'withdrawals', 'availableForWithdrawal'));
    }

    public function createWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'bank_details' => 'required|string|min:10',
        ]);

        $user = Auth::user();
        $wallet = $user->getWallet();
        
        // Calculate available balance from ended events
        $events = $user->events()->where('ends_at', '<', now())->get();
        $eventIds = $events->pluck('id')->toArray();
        
        $availableForWithdrawal = 0;
        if ($events->count() > 0) {
            $totalRevenue = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->sum('amount');
            $twoDawnFee = 0;
            $orders = Order::whereIn('event_id', $eventIds)->where('status', 'paid')->get();
            foreach ($orders as $order) {
                $fee = ($order->amount * 0.05) + 5000;
                $twoDawnFee += $fee;
            }
            $availableForWithdrawal = max(0, ($totalRevenue - $twoDawnFee) / 100);
        }
        
        if ($request->amount > $availableForWithdrawal) {
            return back()->withErrors(['amount' => 'Insufficient available balance. You can only withdraw from ended events.']);
        }
        
        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'bank_details' => $request->bank_details,
        ]);
        
        return back()->with('status', 'Withdrawal request submitted successfully!');
    }
}
