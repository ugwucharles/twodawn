import { useState } from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router-dom';
import { Search, Bell, Settings, LogOut, Menu, X, Activity, Users, Calendar, DollarSign, BarChart3, Shield, Database, Globe } from 'lucide-react';

const sidebarItems = [
  { name: 'Dashboard', path: '/admin/dashboard', icon: BarChart3 },
  { name: 'Live Activity', path: '/admin/activity', icon: Activity },
  { name: 'Events', path: '/admin/events', icon: Calendar },
  { name: 'Organizers', path: '/admin/organizers', icon: Users },
  { name: 'Users', path: '/admin/users', icon: Users },
  { name: 'Orders', path: '/admin/orders', icon: DollarSign },
  { name: 'Transactions', path: '/admin/transactions', icon: DollarSign },
  { name: 'Analytics', path: '/admin/analytics', icon: BarChart3 },
  { name: 'System Health', path: '/admin/health', icon: Database },
  { name: 'Settings', path: '/admin/settings', icon: Settings },
];

function AdminLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [searchOpen, setSearchOpen] = useState(false);
  const [notificationsOpen, setNotificationsOpen] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-gray-950 text-gray-100">
      {/* Sidebar */}
      <aside className={`fixed left-0 top-0 h-full w-64 bg-gray-900 border-r border-gray-800 transition-transform duration-300 z-50 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="p-6 border-b border-gray-800">
            <Link to="/admin/dashboard" className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-xl">2D</span>
              </div>
              <div>
                <h1 className="text-xl font-bold text-white">2DAWN</h1>
                <p className="text-xs text-gray-400">Command Center</p>
              </div>
            </Link>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
            {sidebarItems.map((item) => {
              const Icon = item.icon;
              const isActive = location.pathname === item.path || location.pathname.startsWith(item.path + '/');
              return (
                <Link
                  key={item.path}
                  to={item.path}
                  className={`flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors ${
                    isActive
                      ? 'bg-purple-600/20 text-purple-400 border border-purple-500/30'
                      : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200'
                  }`}
                >
                  <Icon className="w-5 h-5" />
                  <span className="font-medium">{item.name}</span>
                </Link>
              );
            })}
          </nav>

          {/* User Section */}
          <div className="p-4 border-t border-gray-800">
            <div className="flex items-center space-x-3 mb-4">
              <div className="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-700 rounded-full flex items-center justify-center">
                <span className="text-white font-bold">SA</span>
              </div>
              <div className="flex-1">
                <p className="text-sm font-medium text-white">Super Admin</p>
                <p className="text-xs text-gray-400">System Owner</p>
              </div>
            </div>
            <button
              onClick={handleLogout}
              className="flex items-center space-x-2 w-full px-4 py-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
            >
              <LogOut className="w-4 h-4" />
              <span className="text-sm">Logout</span>
            </button>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <div className={`transition-all duration-300 ${sidebarOpen ? 'ml-64' : 'ml-0'}`}>
        {/* Top Navigation */}
        <header className="sticky top-0 z-40 bg-gray-950/80 backdrop-blur-xl border-b border-gray-800">
          <div className="flex items-center justify-between px-6 py-4">
            <div className="flex items-center space-x-4">
              <button
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
              >
                {sidebarOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
              </button>
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search everything..."
                  className="w-64 pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                  onFocus={() => setSearchOpen(true)}
                />
              </div>
            </div>

            <div className="flex items-center space-x-4">
              {/* Notifications */}
              <button
                onClick={() => setNotificationsOpen(!notificationsOpen)}
                className="relative p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
              >
                <Bell className="w-5 h-5" />
                <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
              </button>

              {/* Quick Actions */}
              <div className="flex items-center space-x-2">
                <Link
                  to="/"
                  target="_blank"
                  className="px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors flex items-center space-x-2"
                >
                  <Globe className="w-4 h-4" />
                  <span>View Site</span>
                </Link>
              </div>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className="p-6">
          <Outlet />
        </main>
      </div>

      {/* Search Modal */}
      {searchOpen && (
        <div
          className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-start justify-center pt-20"
          onClick={() => setSearchOpen(false)}
        >
          <div
            className="w-full max-w-2xl bg-gray-900 border border-gray-800 rounded-xl shadow-2xl"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="p-4 border-b border-gray-800">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search events, users, organizers, transactions..."
                  className="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                  autoFocus
                />
              </div>
            </div>
            <div className="p-4">
              <p className="text-sm text-gray-400 text-center">Type to search across the platform</p>
            </div>
          </div>
        </div>
      )}

      {/* Notifications Panel */}
      {notificationsOpen && (
        <div
          className="fixed top-16 right-6 w-80 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl z-50"
          onClick={(e) => e.stopPropagation()}
        >
          <div className="p-4 border-b border-gray-800">
            <h3 className="font-semibold text-white">Notifications</h3>
          </div>
          <div className="p-4 max-h-96 overflow-y-auto">
            <p className="text-sm text-gray-400 text-center py-4">No new notifications</p>
          </div>
        </div>
      )}
    </div>
  );
}

export default AdminLayout;
