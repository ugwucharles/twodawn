<x-organizer-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-10">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Wallet</h1>
            <p class="text-gray-500 mt-1 font-medium">Manage your earnings and withdrawals</p>
        </div>

        <!-- Wallet Balance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <!-- Current Balance -->
            <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50">
                <p class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-2">Current Balance</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-gray-900">₦</span>
                    <p class="text-5xl font-black text-gray-900">{{ number_format($wallet->balance, 2) }}</p>
                </div>
                <p class="text-xs text-gray-600 mt-2 font-medium">After 2DAWN fees</p>
            </div>

            <!-- Available for Withdrawal -->
            <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50">
                <p class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-2">Available for Withdrawal</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black text-gray-900">₦</span>
                    <p class="text-5xl font-black text-gray-900">{{ number_format($availableForWithdrawal, 2) }}</p>
                </div>
                <p class="text-xs text-gray-600 mt-2 font-medium">From ended events only</p>
            </div>
        </div>

        <!-- Withdrawal Form -->
        <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 mb-10">
            <h2 class="text-xl font-black text-gray-900 mb-6">Request Withdrawal</h2>
            
            @if($availableForWithdrawal < 100)
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 mb-6">
                    <p class="text-sm font-semibold text-yellow-800">Minimum withdrawal amount is ₦100. You need more ended events to withdraw.</p>
                </div>
            @endif

            <form action="{{ route('organizer.wallet.withdraw') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Amount (₦)</label>
                        <input type="number" 
                               name="amount" 
                               min="100" 
                               max="{{ $availableForWithdrawal }}" 
                               step="0.01"
                               class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                               placeholder="Enter amount"
                               @if($availableForWithdrawal < 100) disabled @endif>
                        <p class="text-xs text-gray-500 mt-1">Maximum: ₦{{ number_format($availableForWithdrawal, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Bank Details</label>
                        <textarea name="bank_details" 
                                  rows="3"
                                  class="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                  placeholder="Account Name, Account Number, Bank Name"
                                  @if($availableForWithdrawal < 100) disabled @endif required></textarea>
                    </div>
                </div>
                <button type="submit" 
                        class="mt-6 px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        @if($availableForWithdrawal < 100) disabled @endif>
                    Submit Withdrawal Request
                </button>
            </form>
        </div>

        <!-- Withdrawal History -->
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 overflow-hidden">
            <div class="p-8 border-b border-gray-50">
                <h2 class="text-xl font-black text-gray-900">Withdrawal History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[11px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                            <th class="px-8 py-5">Date</th>
                            <th class="px-8 py-5">Amount</th>
                            <th class="px-8 py-5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-6 text-sm font-semibold text-gray-500">{{ $withdrawal->created_at->format('M j, Y - g:i A') }}</td>
                            <td class="px-8 py-6 text-sm font-black text-gray-900">₦{{ number_format($withdrawal->amount, 2) }}</td>
                            <td class="px-8 py-6">
                                @if($withdrawal->status === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                        Pending
                                    </span>
                                @elseif($withdrawal->status === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        Approved
                                    </span>
                                @elseif($withdrawal->status === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-8 py-20 text-center text-gray-400 font-medium italic">No withdrawal history yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($withdrawals->hasPages())
                <div class="p-4 border-t border-gray-50">
                    {{ $withdrawals->links() }}
                </div>
            @endif
        </div>
    </div>
</x-organizer-layout>
