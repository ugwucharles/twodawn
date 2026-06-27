import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { TrendingUp, TrendingDown, DollarSign, Ticket, Calendar, Users, Activity, AlertCircle, ArrowUpRight, ArrowDownRight } from 'lucide-react';

function AdminDashboard() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboard();
  }, []);

  const fetchDashboard = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_BACKEND_URL}/admin/dashboard`);
      setStats(response.data);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load dashboard', err);
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
    return `₦${((amount || 0) / 100).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  };

  const statCards = [
    {
      title: 'Total Revenue',
      value: formatCurrency(stats?.stats?.revenue_total || 0),
      change: '+12.5%',
      trend: 'up',
      icon: DollarSign,
      color: 'green',
    },
    {
      title: 'Revenue Today',
      value: formatCurrency(stats?.stats?.revenue_today || 0),
      change: '+8.2%',
      trend: 'up',
      icon: DollarSign,
      color: 'purple',
    },
    {
      title: 'Tickets Sold Today',
      value: stats?.stats?.tickets_today || 0,
      change: '+15.3%',
      trend: 'up',
      icon: Ticket,
      color: 'blue',
    },
    {
      title: 'Total Tickets Sold',
      value: stats?.stats?.tickets_total || 0,
      change: '+22.1%',
      trend: 'up',
      icon: Ticket,
      color: 'cyan',
    },
    {
      title: 'Active Events',
      value: stats?.stats?.events_active || 0,
      change: '+5.0%',
      trend: 'up',
      icon: Calendar,
      color: 'orange',
    },
    {
      title: 'Total Organizers',
      value: stats?.stats?.organizers_total || 0,
      change: '+3.2%',
      trend: 'up',
      icon: Users,
      color: 'pink',
    },
    {
      title: 'Total Users',
      value: stats?.stats?.users_total || 0,
      change: '+18.7%',
      trend: 'up',
      icon: Users,
      color: 'indigo',
    },
    {
      title: 'Failed Payments',
      value: stats?.stats?.payments_failed || 0,
      change: '-2.1%',
      trend: 'down',
      icon: AlertCircle,
      color: 'red',
    },
  ];

  const colorClasses = {
    green: 'from-green-500/20 to-green-600/20 border-green-500/30',
    purple: 'from-purple-500/20 to-purple-600/20 border-purple-500/30',
    blue: 'from-blue-500/20 to-blue-600/20 border-blue-500/30',
    cyan: 'from-cyan-500/20 to-cyan-600/20 border-cyan-500/30',
    orange: 'from-orange-500/20 to-orange-600/20 border-orange-500/30',
    pink: 'from-pink-500/20 to-pink-600/20 border-pink-500/30',
    indigo: 'from-indigo-500/20 to-indigo-600/20 border-indigo-500/30',
    red: 'from-red-500/20 to-red-600/20 border-red-500/30',
  };

  const iconColorClasses = {
    green: 'text-green-400',
    purple: 'text-purple-400',
    blue: 'text-blue-400',
    cyan: 'text-cyan-400',
    orange: 'text-orange-400',
    pink: 'text-pink-400',
    indigo: 'text-indigo-400',
    red: 'text-red-400',
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-white">Mission Control</h1>
        <p className="text-gray-400 mt-1">Overview of the 2DAWN ecosystem</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {statCards.map((stat, index) => {
          const Icon = stat.icon;
          return (
            <div
              key={index}
              className={`bg-gradient-to-br ${colorClasses[stat.color]} border rounded-xl p-6 hover:scale-105 transition-transform duration-200`}
            >
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <p className="text-sm font-medium text-gray-400">{stat.title}</p>
                  <p className="text-3xl font-bold text-white mt-2">{stat.value}</p>
                  <div className="flex items-center mt-3 space-x-1">
                    {stat.trend === 'up' ? (
                      <ArrowUpRight className="w-4 h-4 text-green-400" />
                    ) : (
                      <ArrowDownRight className="w-4 h-4 text-red-400" />
                    )}
                    <span className={`text-sm font-medium ${stat.trend === 'up' ? 'text-green-400' : 'text-red-400'}`}>
                      {stat.change}
                    </span>
                </div>
                </div>
                <div className={`p-3 rounded-lg bg-gray-900/50 ${iconColorClasses[stat.color]}`}>
                  <Icon className="w-6 h-6" />
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue Chart */}
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-white">Revenue Trend</h2>
            <select className="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-gray-300 focus:outline-none focus:border-purple-500">
              <option>Last 14 days</option>
              <option>Last 30 days</option>
              <option>Last 90 days</option>
            </select>
          </div>
          <div className="h-64 flex items-center justify-center text-gray-500">
            <div className="text-center">
              <Activity className="w-12 h-12 mx-auto mb-2 opacity-50" />
              <p>Revenue chart visualization</p>
              <p className="text-sm mt-1">Chart library integration pending</p>
            </div>
          </div>
        </div>

        {/* Ticket Sales Chart */}
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-white">Ticket Sales</h2>
            <select className="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-gray-300 focus:outline-none focus:border-purple-500">
              <option>Last 14 days</option>
              <option>Last 30 days</option>
              <option>Last 90 days</option>
            </select>
          </div>
          <div className="h-64 flex items-center justify-center text-gray-500">
            <div className="text-center">
              <Ticket className="w-12 h-12 mx-auto mb-2 opacity-50" />
              <p>Ticket sales chart visualization</p>
              <p className="text-sm mt-1">Chart library integration pending</p>
            </div>
          </div>
        </div>
      </div>

      {/* Recent Activity & Upcoming Events */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Recent Activity */}
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-white">Live Activity</h2>
            <Link to="/admin/activity" className="text-sm text-purple-400 hover:text-purple-300">
              View all
            </Link>
          </div>
          <div className="space-y-4">
            {stats?.activity?.length > 0 ? (
              stats.activity.slice(0, 5).map((activity, index) => (
                <div key={index} className="flex items-start space-x-3 p-3 bg-gray-800/50 rounded-lg">
                  <div className="w-2 h-2 mt-2 rounded-full bg-purple-500"></div>
                  <div className="flex-1">
                    <p className="text-sm text-gray-300">{activity.action}</p>
                    <p className="text-xs text-gray-500 mt-1">
                      {new Date(activity.created_at).toLocaleString()}
                    </p>
                  </div>
                </div>
              ))
            ) : (
              <p className="text-sm text-gray-500 text-center py-4">No recent activity</p>
            )}
          </div>
        </div>

        {/* Upcoming Events */}
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-white">Upcoming Events</h2>
            <Link to="/admin/events" className="text-sm text-purple-400 hover:text-purple-300">
              View all
            </Link>
          </div>
          <div className="space-y-4">
            {stats?.upcoming?.length > 0 ? (
              stats.upcoming.map((event, index) => (
                <Link
                  key={index}
                  to={`/admin/events/${event.id}`}
                  className="block p-3 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
                >
                  <p className="text-sm font-medium text-white">{event.title}</p>
                  <p className="text-xs text-gray-500 mt-1">
                    {new Date(event.starts_at).toLocaleDateString()} • {event.venue}
                  </p>
                </Link>
              ))
            ) : (
              <p className="text-sm text-gray-500 text-center py-4">No upcoming events</p>
            )}
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 className="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <Link
            to="/admin/events"
            className="flex items-center space-x-3 p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
          >
            <Calendar className="w-5 h-5 text-purple-400" />
            <span className="text-sm text-gray-300">Manage Events</span>
          </Link>
          <Link
            to="/admin/organizers"
            className="flex items-center space-x-3 p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
          >
            <Users className="w-5 h-5 text-blue-400" />
            <span className="text-sm text-gray-300">View Organizers</span>
          </Link>
          <Link
            to="/admin/transactions"
            className="flex items-center space-x-3 p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
          >
            <DollarSign className="w-5 h-5 text-green-400" />
            <span className="text-sm text-gray-300">Transactions</span>
          </Link>
          <Link
            to="/admin/health"
            className="flex items-center space-x-3 p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
          >
            <Activity className="w-5 h-5 text-orange-400" />
            <span className="text-sm text-gray-300">System Health</span>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default AdminDashboard;
