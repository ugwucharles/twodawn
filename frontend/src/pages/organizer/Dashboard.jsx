import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import { Calendar, Ticket, Wallet, DollarSign, Plus, ChevronRight } from 'lucide-react';

function OrganizerDashboard() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    console.log('Dashboard component mounted');
    fetchDashboard();
  }, []);

  const fetchDashboard = async () => {
    try {
      console.log('Fetching dashboard data...');
      const response = await api.get('/organizer/dashboard');
      console.log('Dashboard response:', response.data);
      console.log('Response structure:', {
        hasData: !!response.data,
        hasStats: !!response.data?.stats,
        hasEvents: !!response.data?.events,
        hasRecentOrders: !!response.data?.recent_orders
      });
      setStats(response.data);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load dashboard', err);
      console.error('Error response:', err.response?.data);
      setError('Failed to load dashboard');
      setLoading(false);
    }
  };

  console.log('Dashboard render state:', { loading, error, hasStats: !!stats });

  if (loading) {
    console.log('Dashboard: showing loading state');
    return <div className="text-center py-12">Loading dashboard...</div>;
  }

  if (error) {
    console.log('Dashboard: showing error state', error);
    return <div className="text-center py-12 text-red-500">{error}</div>;
  }

  if (!stats) {
    console.log('Dashboard: no stats data');
    return <div className="text-center py-12">No data available</div>;
  }

  const totalEvents = stats?.stats?.total_events || 0;
  const upcomingEvents = stats?.stats?.upcoming_events || 0;
  const totalTicketsSold = stats?.stats?.total_tickets_sold || 0;
  const walletBalance = stats?.stats?.wallet_balance || 0;
  const totalRevenue = stats?.stats?.total_revenue || 0;
  const events = stats?.events || [];
  const recentOrders = stats?.recent_orders || [];

  console.log('Dashboard: rendering with data', { totalEvents, eventsCount: events.length, recentOrdersCount: recentOrders.length });

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
      {/* Header Section */}
      <div className="mb-10">
        <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Organizer Dashboard</h1>
        <p className="text-gray-500 mt-1 font-medium">Welcome back! Here's what's happening with your events today.</p>
      </div>

      {/* Stats Grid - 4 Cards Per Row */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        {/* Total Events */}
        <div className="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 flex flex-col justify-between min-h-[140px]">
          <div>
            <p className="text-sm font-medium text-gray-500 mb-2">Total Events</p>
            <p className="text-3xl font-bold text-gray-900">{totalEvents}</p>
          </div>
          {upcomingEvents > 0 && (
            <div className="mt-4">
              <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                {upcomingEvents} Upcoming
              </span>
            </div>
          )}
        </div>

        {/* Tickets Sold */}
        <div className="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 flex flex-col justify-between min-h-[140px]">
          <div>
            <p className="text-sm font-medium text-gray-500 mb-2">Tickets Sold</p>
            <p className="text-3xl font-bold text-gray-900">{totalTicketsSold}</p>
          </div>
        </div>

        {/* Wallet Balance */}
        <div className="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 flex flex-col justify-between min-h-[140px]">
          <div>
            <p className="text-sm font-medium text-gray-500 mb-2">Wallet Balance</p>
            <div className="flex items-baseline gap-1">
              <span className="text-xl font-bold text-gray-900">₦</span>
              <p className="text-3xl font-bold text-gray-900">{walletBalance.toFixed(2)}</p>
            </div>
          </div>
          <div className="mt-4">
            <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
              Available
            </span>
          </div>
        </div>

        {/* Gross Revenue */}
        <div className="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 flex flex-col justify-between min-h-[140px]">
          <div>
            <p className="text-sm font-medium text-gray-500 mb-2">Gross Revenue</p>
            <p className="text-3xl font-bold text-gray-900">₦{(totalRevenue / 100).toFixed(2)}</p>
          </div>
        </div>
      </div>

      {/* Charts Central */}
      <div className="mb-10">
        {/* Capacity Pie Only */}
        <div className="w-full bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 flex flex-col">
          <h2 className="text-xl font-bold text-gray-900 mb-2">Ticket Analytics</h2>
          <p className="text-sm text-gray-500 font-medium mb-8">Overall sales performance</p>

          <div className="flex-1 flex flex-col items-center justify-center relative">
            <div className="relative h-56 w-56 mx-auto">
              <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span className="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sold</span>
                <span className="text-3xl font-bold text-gray-900">
                  {totalTicketsSold > 0 ? Math.round((totalTicketsSold / (totalTicketsSold + 100)) * 100) : 0}%
                </span>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4 mt-8">
            <div className="p-4 bg-gray-50 rounded-xl border border-gray-100">
              <span className="text-xs font-semibold text-gray-500 block mb-1">Sold</span>
              <span className="text-lg font-bold text-gray-900">{totalTicketsSold}</span>
            </div>
            <div className="p-4 bg-gray-50 rounded-xl border border-gray-100">
              <span className="text-xs font-semibold text-gray-500 block mb-1">Left</span>
              <span className="text-lg font-bold text-gray-900">100</span>
            </div>
          </div>
        </div>
      </div>

      {/* Recent Activity Sections */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* Events Summary */}
        <div className="lg:col-span-5 bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 animate-slide-up flex flex-col justify-between">
          <div className="flex justify-between items-center mb-8">
            <h2 className="text-xl font-bold text-gray-900">Your Events</h2>
            <Link to="/organizer/events/create" className="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-200 hover:scale-105 transition-transform">
              <Plus className="w-5 h-5" />
            </Link>
          </div>

          <div className="space-y-4">
            {events.filter(e => e.is_published).length > 0 ? (
              events.filter(e => e.is_published).slice(0, 4).map((event) => (
                <div key={event.id} className="flex items-center p-4 bg-white rounded-xl border border-gray-100/50 hover:bg-gray-50/50 transition-all duration-300">
                  <div className="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 flex flex-col items-center justify-center font-bold text-gray-900 shrink-0">
                    {event.starts_at ? (
                      <>
                        <span className="text-base text-gray-900">{new Date(event.starts_at).getDate()}</span>
                        <span className="text-[8px] uppercase tracking-tighter -mt-1 opacity-70 text-gray-500">
                          {new Date(event.starts_at).toLocaleString('default', { month: 'short' })}
                        </span>
                      </>
                    ) : (
                      <span className="text-[10px] text-gray-500">TBD</span>
                    )}
                  </div>
                  <div className="flex-1 mx-4 min-w-0">
                    <h3 className="font-bold text-gray-900 text-sm truncate">{event.title}</h3>
                    <div className="flex items-center gap-3 mt-1">
                      <span className="text-xs font-semibold text-gray-500">
                        {event.orders_count || 0} Sold
                      </span>
                      <div className="w-1.5 h-1.5 bg-gray-300 rounded-full"></div>
                      <span className="text-xs font-semibold text-green-600">Active</span>
                    </div>
                  </div>
                  <Link to={`/organizer/events/${event.id}`} className="p-2 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-all duration-300">
                    <ChevronRight className="w-4 h-4" />
                  </Link>
                </div>
              ))
            ) : (
              <div className="py-12 flex flex-col items-center justify-center grayscale opacity-50">
                <Calendar className="w-12 h-12 text-black mb-2" />
                <p className="text-xs font-bold text-black uppercase tracking-widest">No published events yet</p>
              </div>
            )}
          </div>
        </div>

        {/* Transactions */}
        <div className="lg:col-span-7 bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-purple-200">
          <div className="p-8 border-b border-gray-100/50 flex justify-between items-center bg-gray-50">
            <div>
              <h2 className="text-xl font-bold text-gray-900">Recent Transactions</h2>
              <p className="text-sm text-gray-500 font-medium">Latest payments received</p>
            </div>
            <Link to="/organizer/orders" className="text-xs font-bold text-blue-600 hover:text-blue-700 bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-sm transition-all">
              View All
            </Link>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left">
              <thead>
                <tr className="text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                  <th className="px-8 py-5">Event</th>
                  <th className="px-8 py-5">Date</th>
                  <th className="px-8 py-5 text-right">Amount</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {recentOrders.length > 0 ? (
                  recentOrders.map((order) => (
                    <tr key={order.id} className="group hover:bg-gray-50/50 transition-colors">
                      <td className="px-8 py-6">
                        <div className="flex flex-col">
                          <span className="text-sm font-bold text-gray-900 leading-tight">
                            {order.event?.title || 'Deleted Event'}
                          </span>
                          <span className="text-xs font-medium text-gray-400 mt-1 uppercase tracking-wider">
                            {order.reference || `TXN-${order.id}`}
                          </span>
                        </div>
                      </td>
                      <td className="px-8 py-6 text-sm font-medium text-gray-500">
                        {new Date(order.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </td>
                      <td className="px-8 py-6 text-right">
                        <span className="text-sm font-bold text-gray-900">₦{(order.amount / 100).toFixed(2)}</span>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="3" className="px-8 py-20 text-center text-gray-500 font-medium italic">
                      No recent transactions.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <style>{`
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        .animate-slide-up { animation: slideUp 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  );
}

export default OrganizerDashboard;
