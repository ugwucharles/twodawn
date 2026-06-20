import { useEffect, useState } from 'react';
import axios from 'axios';
import { ShoppingBag } from 'lucide-react';

function Orders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchOrders();
  }, []);

  const fetchOrders = async () => {
    try {
      const response = await axios.get('/organizer/orders');
      setOrders(response.data.orders || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load orders', err);
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="text-center py-12">Loading orders...</div>;
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-black text-gray-900 tracking-tight">Orders</h1>
        <p className="text-gray-500 mt-1 font-medium">View all your event orders</p>
      </div>

      {/* Orders Table */}
      <div className="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 overflow-hidden">
        {orders.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead>
                <tr className="text-[11px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                  <th className="px-8 py-5">Order ID</th>
                  <th className="px-8 py-5">Event</th>
                  <th className="px-8 py-5">Customer</th>
                  <th className="px-8 py-5">Tickets</th>
                  <th className="px-8 py-5">Amount</th>
                  <th className="px-8 py-5">Date</th>
                  <th className="px-8 py-5">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50">
                {orders.map((order) => (
                  <tr key={order.id} className="hover:bg-gray-50/50 transition-colors">
                    <td className="px-8 py-6 text-sm font-semibold text-gray-500">{order.reference || order.paystack_reference}</td>
                    <td className="px-8 py-6 text-sm font-black text-gray-900">{order.event?.title || 'Deleted Event'}</td>
                    <td className="px-8 py-6 text-sm font-semibold text-gray-500">
                      <div className="font-bold text-gray-900">{order.buyer_name || 'Unknown'}</div>
                      <div className="text-xs text-gray-400 font-medium">{order.buyer_email || ''}</div>
                    </td>
                    <td className="px-8 py-6 text-sm font-black text-gray-900">{order.quantity}</td>
                    <td className="px-8 py-6 text-sm font-black text-gray-900">₦{((order.amount || 0) / 100).toFixed(2)}</td>
                    <td className="px-8 py-6 text-sm font-semibold text-gray-500">
                      {order.created_at ? new Date(order.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) : ''}
                    </td>
                    <td className="px-8 py-6">
                      <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                        Paid
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="py-20 text-center">
            <ShoppingBag className="h-16 w-16 mx-auto mb-4 text-gray-300" />
            <p className="text-xl font-bold text-gray-900 mb-2">No orders yet</p>
            <p className="text-gray-500">Orders will appear here once customers purchase tickets</p>
          </div>
        )}
      </div>

      <style>{`
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  );
}

export default Orders;
