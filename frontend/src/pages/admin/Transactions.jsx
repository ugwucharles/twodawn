import { useState, useEffect } from 'react';
import api from '../../services/api';
import { DollarSign } from 'lucide-react';

function AdminTransactions() {
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchTransactions();
  }, []);

  const fetchTransactions = async () => {
    try {
      const response = await api.get('/ucc/transactions');
      setTransactions(response.data.transactions || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load transactions', err);
      setLoading(false);
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
        <h1 className="text-3xl font-bold text-white">Transactions</h1>
        <p className="text-gray-400 mt-1">View all payment transactions</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="space-y-4">
          {transactions.length > 0 ? (
            transactions.map((txn, index) => (
              <div key={index} className="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                <div>
                  <p className="text-sm font-medium text-white">{txn.paystack_reference}</p>
                  <p className="text-xs text-gray-500 mt-1">{txn.event_title || 'Unknown Event'}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm text-gray-300">₦{((txn.amount || 0) / 100).toLocaleString()}</p>
                  <p className={`text-xs ${txn.status === 'paid' ? 'text-green-400' : 'text-red-400'}`}>
                    {txn.status}
                  </p>
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-12">
              <DollarSign className="w-12 h-12 mx-auto text-gray-500 mb-4" />
              <p className="text-gray-500">No transactions found</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default AdminTransactions;
