import { useEffect, useState } from 'react';
import api from '../../services/api';
import { Wallet as WalletIcon, Loader2 } from 'lucide-react';

function OrganizerWallet() {
  const [wallet, setWallet] = useState({ balance: 0, available_for_withdrawal: 0 });
  const [withdrawals, setWithdrawals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [banks, setBanks] = useState([]);
  const [withdrawalForm, setWithdrawalForm] = useState({ amount: '', bank_name: '', bank_code: '', account_number: '', account_name: '' });
  const [submitting, setSubmitting] = useState(false);
  const [resolvingAccount, setResolvingAccount] = useState(false);
  const [resolveError, setResolveError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  useEffect(() => {
    fetchWalletData();
    fetchBankList();
  }, []);

  const fetchWalletData = async () => {
    try {
      const response = await api.get('/organizer/wallet');
      setWallet(response.data.wallet || { balance: 0, available_for_withdrawal: 0 });
      setWithdrawals(response.data.withdrawals || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load wallet data', err);
      setLoading(false);
    }
  };

  const fetchBankList = async () => {
    try {
      const response = await api.get('/organizer/bank/list');
      setBanks(response.data.banks || []);
    } catch (err) {
      console.error('Failed to load bank list', err);
    }
  };

  const resolveAccount = async (accountNum, bankCode) => {
    if (!accountNum || accountNum.length !== 10 || !bankCode) return;
    setResolvingAccount(true);
    setResolveError('');
    try {
      const response = await api.get(`/organizer/bank/resolve?account_number=${accountNum}&bank_code=${bankCode}`);
      if (response.data.ok) {
        setWithdrawalForm(prev => ({
          ...prev,
          account_name: response.data.account_name
        }));
      } else {
        setResolveError('Could not verify account name');
        setWithdrawalForm(prev => ({ ...prev, account_name: '' }));
      }
    } catch (err) {
      console.error('Failed to resolve bank account', err);
      setResolveError(err.response?.data?.message || 'Could not verify account name');
      setWithdrawalForm(prev => ({ ...prev, account_name: '' }));
    } finally {
      setResolvingAccount(false);
    }
  };

  const handleBankChange = (e) => {
    const code = e.target.value;
    const selectedBank = banks.find(b => b.code === code);
    const bankName = selectedBank ? selectedBank.name : '';
    setWithdrawalForm(prev => {
      const updated = { ...prev, bank_code: code, bank_name: bankName, account_name: '' };
      resolveAccount(prev.account_number, code);
      return updated;
    });
  };

  const handleAccountNumberChange = (e) => {
    const val = e.target.value.replace(/\D/g, '').substring(0, 10);
    setWithdrawalForm(prev => {
      const updated = { ...prev, account_number: val, account_name: '' };
      if (val.length === 10) {
        resolveAccount(val, prev.bank_code);
      }
      return updated;
    });
  };

  const handleWithdrawalSubmit = async (e) => {
    e.preventDefault();
    if (!withdrawalForm.account_name) {
      alert('Please resolve and verify your account details first.');
      return;
    }
    setSubmitting(true);
    try {
      await api.post('/organizer/wallet/withdraw', withdrawalForm);
      setSuccessMessage('Withdrawal request submitted successfully!');
      setWithdrawalForm({ amount: '', bank_name: '', bank_code: '', account_number: '', account_name: '' });
      fetchWalletData();
      setTimeout(() => setSuccessMessage(''), 5000);
    } catch (err) {
      console.error('Failed to submit withdrawal', err);
      alert('Failed to submit withdrawal request');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return <div className="text-center py-12">Loading wallet...</div>;
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
      {/* Header */}
      <div className="mb-10">
        <h1 className="text-3xl font-black text-gray-900 tracking-tight">Wallet</h1>
        <p className="text-gray-500 mt-1 font-medium">Manage your earnings and withdrawals</p>
      </div>

      {successMessage && (
        <div className="mb-8 p-5 bg-green-50 text-green-700 rounded-2xl text-sm border border-green-200 font-medium">
          {successMessage}
        </div>
      )}

      {/* Wallet Balance Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        {/* Current Balance */}
        <div className="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200">
          <p className="text-sm font-bold text-gray-900 uppercase tracking-widest mb-2">Current Balance</p>
          <div className="flex items-baseline gap-1">
            <span className="text-3xl font-black text-gray-900">₦</span>
            <p className="text-5xl font-black text-gray-900">{wallet.balance.toFixed(2)}</p>
          </div>
          <p className="text-xs text-gray-600 mt-2 font-medium">After 2DAWN fees</p>
        </div>

        {/* Available for Withdrawal */}
        <div className="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200">
          <p className="text-sm font-bold text-gray-900 uppercase tracking-widest mb-2">Available for Withdrawal</p>
          <div className="flex items-baseline gap-1">
            <span className="text-3xl font-black text-gray-900">₦</span>
            <p className="text-5xl font-black text-gray-900">{wallet.available_for_withdrawal.toFixed(2)}</p>
          </div>
          <p className="text-xs text-gray-600 mt-2 font-medium">From ended events only</p>
        </div>
      </div>

      {/* Withdrawal Form */}
      <div className="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 mb-10">
        <h2 className="text-xl font-black text-gray-900 mb-6">Request Withdrawal</h2>
        
        <form onSubmit={handleWithdrawalSubmit}>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-2">Amount (₦)</label>
              <input
                type="number"
                value={withdrawalForm.amount}
                onChange={(e) => setWithdrawalForm({ ...withdrawalForm, amount: e.target.value })}
                min="0.01"
                max={wallet.available_for_withdrawal}
                step="0.01"
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                placeholder="Enter amount"
                required
              />
              <p className="text-xs text-gray-500 mt-1">Maximum: ₦{wallet.available_for_withdrawal.toFixed(2)}</p>
            </div>
            
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Select Bank</label>
                <select
                  value={withdrawalForm.bank_code}
                  onChange={handleBankChange}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm bg-white"
                  required
                >
                  <option value="">-- Choose Bank --</option>
                  {banks.map((b) => (
                    <option key={b.code} value={b.code}>
                      {b.name}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-bold text-gray-700 mb-1">Account Number (10 Digits)</label>
                <input
                  type="text"
                  pattern="[0-9]{10}"
                  value={withdrawalForm.account_number}
                  onChange={handleAccountNumberChange}
                  className="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm"
                  placeholder="e.g. 0123456789"
                  disabled={!withdrawalForm.bank_code}
                  required
                />
              </div>
              <div>
                <div className="flex items-center justify-between mb-1">
                  <label className="block text-sm font-bold text-gray-700">Account Name</label>
                  {resolvingAccount && (
                    <span className="flex items-center text-xs text-blue-600 font-medium">
                      <Loader2 className="w-3 h-3 animate-spin mr-1" />
                      Resolving...
                    </span>
                  )}
                </div>
                <input
                  type="text"
                  value={withdrawalForm.account_name}
                  className={`w-full px-4 py-2.5 rounded-xl border transition-all text-sm font-semibold bg-gray-50 text-gray-700 cursor-not-allowed ${
                    resolveError ? 'border-red-200 focus:border-red-500' : 'border-gray-200'
                  }`}
                  placeholder={resolvingAccount ? "Verifying..." : "Auto-resolved from bank details"}
                  disabled
                  required
                />
                {resolveError && (
                  <p className="text-xs text-red-500 mt-1 font-semibold">{resolveError}</p>
                )}
                {withdrawalForm.account_name && !resolvingAccount && (
                  <p className="text-xs text-green-600 mt-1 font-semibold">✓ Verified Account Name</p>
                )}
              </div>
            </div>
          </div>
          <button
            type="submit"
            disabled={submitting}
            className="mt-6 px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {submitting ? 'Submitting...' : 'Submit Withdrawal Request'}
          </button>
        </form>
      </div>

      {/* Withdrawal History */}
      <div className="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 overflow-hidden">
        <div className="p-8 border-b border-gray-50">
          <h2 className="text-xl font-black text-gray-900">Withdrawal History</h2>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="text-[11px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                <th className="px-8 py-5">Date</th>
                <th className="px-8 py-5">Amount</th>
                <th className="px-8 py-5">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {withdrawals.length > 0 ? (
                withdrawals.map((withdrawal) => (
                  <tr key={withdrawal.id} className="hover:bg-gray-50/50 transition-colors">
                    <td className="px-8 py-6 text-sm font-semibold text-gray-500">
                      {new Date(withdrawal.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })}
                    </td>
                    <td className="px-8 py-6 text-sm font-black text-gray-900">₦{withdrawal.amount.toFixed(2)}</td>
                    <td className="px-8 py-6">
                      {withdrawal.status === 'pending' && (
                        <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                          Pending
                        </span>
                      )}
                      {withdrawal.status === 'approved' && (
                        <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                          Approved
                        </span>
                      )}
                      {withdrawal.status === 'rejected' && (
                        <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                          Rejected
                        </span>
                      )}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="3" className="px-8 py-20 text-center text-gray-400 font-medium italic">No withdrawal history yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <style>{`
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  );
}

export default OrganizerWallet;
