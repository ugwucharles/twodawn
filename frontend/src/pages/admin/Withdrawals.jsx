import { useState, useEffect } from 'react';
import api from '../../services/api';
import { DollarSign, Landmark, Check, X, Users, Calendar } from 'lucide-react';

function AdminWithdrawals() {
  const [withdrawals, setWithdrawals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [actioningId, setActioningId] = useState(null);

  useEffect(() => {
    fetchWithdrawals();
  }, []);

  const fetchWithdrawals = async () => {
    try {
      const response = await api.get('/ucc/withdrawals');
      setWithdrawals(response.data.withdrawals || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load withdrawals', err);
      setLoading(false);
    }
  };

  const handleAction = async (id, action) => {
    setActioningId(id);
    try {
      await api.patch(`/ucc/withdrawals/${id}/${action}`);
      // Refresh list
      await fetchWithdrawals();
    } catch (err) {
      console.error(`Failed to ${action} withdrawal request`, err);
      alert(`Failed to ${action} request`);
    } finally {
      setActioningId(null);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-white">Withdrawal Requests</h1>
        <p className="text-gray-400 mt-1">Review and process withdrawals submitted by organizers</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-gray-800/50 text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-800">
                <th className="px-6 py-4">Organizer</th>
                <th className="px-6 py-4">Bank Details</th>
                <th className="px-6 py-4">Amount</th>
                <th className="px-6 py-4">Requested Date</th>
                <th className="px-6 py-4 text-center">Status / Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-800/50">
              {withdrawals.length > 0 ? (
                withdrawals.map((w) => (
                  <tr key={w.id} className="hover:bg-gray-800/20 transition-colors">
                    <td className="px-6 py-5">
                      <div className="flex items-center space-x-3">
                        <div className="p-2 bg-purple-500/10 text-purple-400 rounded-lg">
                          <Users className="w-5 h-5" />
                        </div>
                        <div>
                          <p className="text-sm font-semibold text-white">{w.organizer_name || 'Organizer'}</p>
                          <p className="text-xs text-gray-500">{w.organizer_email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-5">
                      <div className="flex items-start space-x-2">
                        <Landmark className="w-4 h-4 text-gray-500 mt-0.5" />
                        <div className="text-xs">
                          <p className="font-medium text-white">{w.account_name}</p>
                          <p className="text-gray-400 font-mono mt-0.5">{w.account_number} · {w.bank_name}</p>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-5 font-bold text-sm text-white">
                      ₦{(w.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </td>
                    <td className="px-6 py-5 text-xs text-gray-400">
                      <div className="flex items-center space-x-1.5">
                        <Calendar className="w-3.5 h-3.5 text-gray-500" />
                        <span>{new Date(w.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                      </div>
                    </td>
                    <td className="px-6 py-5 text-center">
                      {w.status === 'pending' ? (
                        <div className="flex items-center justify-center space-x-2">
                          <button
                            onClick={() => handleAction(w.id, 'approve')}
                            disabled={actioningId !== null}
                            className="inline-flex items-center space-x-1 px-3 py-1.5 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white rounded-lg text-xs font-bold transition-all disabled:opacity-50"
                          >
                            <Check className="w-3.5 h-3.5" />
                            <span>Approve</span>
                          </button>
                          <button
                            onClick={() => handleAction(w.id, 'reject')}
                            disabled={actioningId !== null}
                            className="inline-flex items-center space-x-1 px-3 py-1.5 bg-rose-600/20 hover:bg-rose-600 text-rose-400 hover:text-white rounded-lg text-xs font-bold transition-all disabled:opacity-50"
                          >
                            <X className="w-3.5 h-3.5" />
                            <span>Reject</span>
                          </button>
                        </div>
                      ) : (
                        <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${
                          w.status === 'approved' 
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                            : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                        }`}>
                          {w.status.charAt(0).toUpperCase() + w.status.slice(1)}
                        </span>
                      )}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="5" className="text-center py-20">
                    <DollarSign className="w-12 h-12 mx-auto text-gray-500 mb-4" />
                    <p className="text-gray-400 font-medium">No withdrawal requests found</p>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

export default AdminWithdrawals;
