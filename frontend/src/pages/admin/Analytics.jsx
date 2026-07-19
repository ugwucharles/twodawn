import { useEffect, useState } from 'react';
import api from '../../services/api';
import { BarChart3, TrendingUp, DollarSign, Ticket, ShoppingBag } from 'lucide-react';

function AdminAnalytics() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchAnalytics();
  }, []);

  const fetchAnalytics = async () => {
    try {
      const dashboardRes = await api.get('/ucc/dashboard');
      const eventsRes = await api.get('/ucc/events/list');
      
      const stats = dashboardRes.data?.stats || {};
      const events = eventsRes.data?.events || [];

      setData({
        stats,
        events,
      });
      setLoading(false);
    } catch (err) {
      console.error('Failed to load analytics data', err);
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

  const formatCurrency = (amount) => {
    return `₦${((amount || 0) / 100).toLocaleString('en-NG', { minimumFractionDigits: 2 })}`;
  };

  const avgOrderValue = data.stats.orders_total > 0 
    ? data.stats.revenue_total / data.stats.orders_total 
    : 0;

  const avgTicketsPerOrder = data.stats.orders_total > 0 
    ? (data.stats.tickets_total / data.stats.orders_total).toFixed(1) 
    : 0;

  const successRate = data.stats.orders_total > 0
    ? ((data.stats.tickets_total / data.stats.orders_total) * 100).toFixed(1)
    : 0;

  const totalAttempts = (data.stats.orders_total || 0) + (data.stats.payments_failed || 0);
  const successPct = totalAttempts > 0 ? Math.round((data.stats.orders_total / totalAttempts) * 100) : 0;

  const funnelItems = [
    { name: 'Orders Attempted', count: totalAttempts, percentage: 100, icon: ShoppingBag, color: 'bg-blue-500' },
    { name: 'Completed Payments', count: data.stats.orders_total || 0, percentage: successPct, icon: DollarSign, color: 'bg-green-500' },
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-white">Analytics Hub</h1>
        <p className="text-gray-400 mt-1">Direct performance insights and conversion rates</p>
      </div>

      {/* Metrics Row */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6 flex items-center space-x-4">
          <div className="p-3 bg-purple-500/10 text-purple-400 rounded-lg">
            <TrendingUp className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm text-gray-400">Average Order Value</p>
            <p className="text-2xl font-bold text-white mt-1">{formatCurrency(avgOrderValue)}</p>
          </div>
        </div>

        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6 flex items-center space-x-4">
          <div className="p-3 bg-green-500/10 text-green-400 rounded-lg">
            <ShoppingBag className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm text-gray-400">Avg Tickets per Order</p>
            <p className="text-2xl font-bold text-white mt-1">{avgTicketsPerOrder}</p>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6">
        {/* Sales Funnel Visualizer */}
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <h2 className="text-lg font-semibold text-white mb-6">Customer Purchase Journey</h2>
          <div className="space-y-6">
            {funnelItems.map((item, idx) => {
              const Icon = item.icon;
              return (
                <div key={idx} className="space-y-2">
                  <div className="flex items-center justify-between text-sm">
                    <div className="flex items-center space-x-2 text-gray-300">
                      <Icon className="w-4 h-4" />
                      <span>{item.name}</span>
                    </div>
                    <div className="font-semibold text-white">
                      {item.count.toLocaleString()} <span className="text-gray-500 text-xs font-normal">({item.percentage}%)</span>
                    </div>
                  </div>
                  <div className="h-3 w-full bg-gray-800 rounded-full overflow-hidden">
                    <div className={`h-full ${item.color} rounded-full`} style={{ width: `${item.percentage}%` }}></div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Top Events Table */}
      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 className="text-lg font-semibold text-white mb-4">Event Performance Metrics</h2>
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-gray-400">
            <thead className="bg-gray-800/50 text-xs uppercase text-gray-400 border-b border-gray-800">
              <tr>
                <th className="py-3 px-4">Event Title</th>
                <th className="py-3 px-4">Venue</th>
                <th className="py-3 px-4">Capacity</th>
                <th className="py-3 px-4">Tickets Sold</th>
                <th className="py-3 px-4 font-semibold text-white">Gross Revenue</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-800/50">
              {data.events.length > 0 ? (
                data.events.map((event, idx) => (
                  <tr key={idx} className="hover:bg-gray-800/20">
                    <td className="py-3 px-4 text-white font-medium">{event.title}</td>
                    <td className="py-3 px-4">{event.venue || 'TBD'}</td>
                    <td className="py-3 px-4">{event.capacity || 'Unlimited'}</td>
                    <td className="py-3 px-4 text-gray-200">{event.tickets_sold || 0}</td>
                    <td className="py-3 px-4 text-purple-400 font-semibold">{formatCurrency(event.revenue || 0)}</td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="5" className="text-center py-6 text-gray-500">No events registered yet</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

export default AdminAnalytics;
