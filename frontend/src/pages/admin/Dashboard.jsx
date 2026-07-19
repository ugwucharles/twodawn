import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import { DollarSign, Ticket, Users, Database, AlertCircle, Landmark } from 'lucide-react';

function CustomAreaChart({ labels = [], data = [], color = 'purple', type = 'number' }) {
  if (!labels.length || !data.length) {
    return (
      <div className="flex items-center justify-center h-full text-gray-500">
        No data available
      </div>
    );
  }

  const maxVal = Math.max(...data, 1);
  const chartHeight = 180;
  const chartWidth = 500;
  const paddingLeft = 55;
  const paddingRight = 15;
  const paddingTop = 15;
  const paddingBottom = 25;

  const graphWidth = chartWidth - paddingLeft - paddingRight;
  const graphHeight = chartHeight - paddingTop - paddingBottom;

  const points = data.map((val, idx) => {
    const x = paddingLeft + (idx / (data.length - 1)) * graphWidth;
    const y = paddingTop + graphHeight - (val / maxVal) * graphHeight;
    return { x, y, value: val };
  });

  const linePath = points.map((p, idx) => `${idx === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
  const areaPath = points.length ? `${linePath} L ${points[points.length - 1].x} ${paddingTop + graphHeight} L ${points[0].x} ${paddingTop + graphHeight} Z` : '';

  const yGridLines = Array.from({ length: 4 }).map((_, idx) => {
    const yVal = (maxVal / 3) * idx;
    const y = paddingTop + graphHeight - (idx / 3) * graphHeight;
    return { y, value: yVal };
  });

  const strokeColor = color === 'purple' ? '#a855f7' : '#3b82f6';
  const fillColor = color === 'purple' ? 'url(#purple-grad)' : 'url(#blue-grad)';

  return (
    <div className="w-full h-full flex items-center justify-center p-2">
      <svg viewBox={`0 0 ${chartWidth} ${chartHeight}`} className="w-full h-full overflow-visible">
        <defs>
          <linearGradient id="purple-grad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="#a855f7" stopOpacity="0.3" />
            <stop offset="100%" stopColor="#a855f7" stopOpacity="0.0" />
          </linearGradient>
          <linearGradient id="blue-grad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stopColor="#3b82f6" stopOpacity="0.3" />
            <stop offset="100%" stopColor="#3b82f6" stopOpacity="0.0" />
          </linearGradient>
        </defs>

        {yGridLines.map((grid, idx) => (
          <g key={idx}>
            <line
              x1={paddingLeft}
              y1={grid.y}
              x2={chartWidth - paddingRight}
              y2={grid.y}
              stroke="#1f2937"
              strokeDasharray="4 4"
            />
            <text
              x={paddingLeft - 8}
              y={grid.y + 3}
              fill="#9ca3af"
              fontSize="9"
              textAnchor="end"
            >
              {type === 'currency'
                ? `₦${(grid.value / 100).toLocaleString('en-US', { maximumFractionDigits: 0 })}`
                : grid.value.toFixed(0)}
            </text>
          </g>
        ))}

        {areaPath && <path d={areaPath} fill={fillColor} />}

        {linePath && (
          <path
            d={linePath}
            fill="none"
            stroke={strokeColor}
            strokeWidth="2.5"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        )}

        {points.map((p, idx) => (
          <g key={idx} className="group cursor-pointer">
            <circle
              cx={p.x}
              cy={p.y}
              r="3.5"
              fill="#111827"
              stroke={strokeColor}
              strokeWidth="2"
            />
            <circle
              cx={p.x}
              cy={p.y}
              r="8"
              fill={strokeColor}
              fillOpacity="0"
              className="hover:fill-opacity-20 transition-all duration-200"
            />
            <title>
              {labels[idx]}: {type === 'currency' ? `₦${(p.value / 100).toLocaleString()}` : p.value}
            </title>
          </g>
        ))}

        {labels.map((label, idx) => {
          if (idx % 3 !== 0 && idx !== labels.length - 1) return null;
          const x = paddingLeft + (idx / (labels.length - 1)) * graphWidth;
          return (
            <text
              key={idx}
              x={x}
              y={chartHeight - 5}
              fill="#9ca3af"
              fontSize="9"
              textAnchor="middle"
            >
              {label}
            </text>
          );
        })}
      </svg>
    </div>
  );
}

function AdminDashboard() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboard();
  }, []);

  const fetchDashboard = async () => {
    try {
      const response = await api.get('/ucc/dashboard');
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
      icon: DollarSign,
      color: 'green',
    },
    {
      title: '2DAWN Platform Earnings',
      value: formatCurrency(stats?.stats?.twodawn_earnings_total || 0),
      icon: Landmark,
      color: 'purple',
    },
    {
      title: 'Revenue Today',
      value: formatCurrency(stats?.stats?.revenue_today || 0),
      icon: DollarSign,
      color: 'blue',
    },
    {
      title: '2DAWN Earnings Today',
      value: formatCurrency(stats?.stats?.twodawn_earnings_today || 0),
      icon: Landmark,
      color: 'cyan',
    },
    {
      title: 'Total Tickets Sold',
      value: stats?.stats?.tickets_total || 0,
      icon: Ticket,
      color: 'orange',
    },
    {
      title: 'Tickets Today',
      value: stats?.stats?.tickets_today || 0,
      icon: Ticket,
      color: 'blue',
    },
    {
      title: 'Total Organizers',
      value: stats?.stats?.organizers_total || 0,
      icon: Users,
      color: 'pink',
    },
    {
      title: 'Failed Payments',
      value: stats?.stats?.payments_failed || 0,
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
    red: 'from-red-500/20 to-red-600/20 border-red-500/30',
  };

  const iconColorClasses = {
    green: 'text-green-400',
    purple: 'text-purple-400',
    blue: 'text-blue-400',
    cyan: 'text-cyan-400',
    orange: 'text-orange-400',
    pink: 'text-pink-400',
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
          </div>
          <div className="h-64 flex items-center justify-center">
            <CustomAreaChart
              labels={stats?.chart?.labels || []}
              data={stats?.chart?.revenue || []}
              color="purple"
              type="currency"
            />
          </div>
        </div>

        {/* Ticket Sales Chart */}
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-white">Ticket Sales</h2>
          </div>
          <div className="h-64 flex items-center justify-center">
            <CustomAreaChart
              labels={stats?.chart?.labels || []}
              data={stats?.chart?.tickets || []}
              color="blue"
              type="number"
            />
          </div>
        </div>
      </div>

      {/* Upcoming Events */}
      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-lg font-semibold text-white">Upcoming Events</h2>
          <Link to="/ucc/events" className="text-sm text-purple-400 hover:text-purple-300">
            View all
          </Link>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {stats?.upcoming?.length > 0 ? (
            stats.upcoming.map((event, index) => (
              <Link
                key={index}
                to={`/ucc/events/${event.id}`}
                className="block p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors border border-gray-700/50"
              >
                <p className="text-sm font-medium text-white">{event.title}</p>
                <p className="text-xs text-gray-500 mt-1">
                  {new Date(event.starts_at).toLocaleDateString('en-NG', { month: 'short', day: 'numeric', year: 'numeric' })} • {event.venue}
                </p>
              </Link>
            ))
          ) : (
            <p className="text-sm text-gray-500 col-span-full text-center py-4">No upcoming events</p>
          )}
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 className="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link
            to="/ucc/organizers"
            className="flex items-center space-x-3 p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
          >
            <Users className="w-5 h-5 text-blue-400" />
            <span className="text-sm text-gray-300">View Organizers</span>
          </Link>
          <Link
            to="/ucc/transactions"
            className="flex items-center space-x-3 p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
          >
            <DollarSign className="w-5 h-5 text-green-400" />
            <span className="text-sm text-gray-300">Transactions</span>
          </Link>
          <Link
            to="/ucc/health"
            className="flex items-center space-x-3 p-4 bg-gray-800/50 rounded-lg hover:bg-gray-800 transition-colors"
          >
            <Database className="w-5 h-5 text-orange-400" />
            <span className="text-sm text-gray-300">System Health</span>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default AdminDashboard;
