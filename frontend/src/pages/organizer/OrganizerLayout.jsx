import React, { useState } from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import { 
  LayoutGrid, 
  Calendar, 
  ShoppingBag, 
  Scan, 
  Wallet, 
  Settings, 
  LogOut,
  ChevronDown
} from 'lucide-react';

export default function OrganizerLayout() {
  const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);
  const location = useLocation();

  const isActive = (path) => {
    return location.pathname === path || location.pathname.startsWith(path + '/');
  };

  const navItems = [
    { path: '/organizer/dashboard', label: 'Dashboard', icon: LayoutGrid },
    { path: '/organizer/events', label: 'Events', icon: Calendar },
    { path: '/organizer/orders', label: 'Orders', icon: ShoppingBag },
    { path: '/organizer/scanner', label: 'Scanner', icon: Scan },
  ];

  const otherItems = [
    { path: '/organizer/wallet', label: 'Wallet', icon: Wallet },
    { path: '/organizer/settings', label: 'Settings', icon: Settings },
  ];

  const handleLogout = () => {
    // TODO: Implement logout
    console.log('Logout');
  };

  return (
    <div className="flex h-screen overflow-hidden bg-white">
      {/* Main Content Container */}
      <div className="flex-1 flex flex-col min-w-0 bg-white overflow-hidden">
        {/* Top Header */}
        <header className="h-20 bg-black flex items-center justify-between px-4 lg:px-12 shrink-0">
          <div className="flex items-center gap-3">
            <Link to="/" className="flex items-center">
              <img src="/logo.svg" alt="2DAWN" className="h-10 w-auto" />
            </Link>
          </div>

          <div className="flex items-center gap-2 lg:gap-6">
            {/* User Profile Card with Dropdown */}
            <div className="relative">
              <button
                onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
                className="flex items-center gap-3 bg-white pl-1 pr-4 py-1 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-all"
              >
                <img
                  src={`https://ui-avatars.com/api/?name=Organizer&background=EBF4FF&color=4299E1`}
                  alt="Organizer"
                  className="w-8 h-8 rounded-xl object-cover"
                />
                <div className="hidden sm:block">
                  <p className="text-xs font-black text-gray-900 leading-tight">Organizer</p>
                  <p className="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Organizer</p>
                </div>
                <ChevronDown className="w-4 h-4 text-black ml-1 transition-transform" />
              </button>

              {/* Dropdown Menu */}
              {profileDropdownOpen && (
                <div className="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                  {/* Navigation links */}
                  {navItems.map((item) => (
                    <Link
                      key={item.path}
                      to={item.path}
                      className="block px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                      onClick={() => setProfileDropdownOpen(false)}
                    >
                      <div className="flex items-center gap-3">
                        <item.icon className="w-4 h-4 text-gray-400" />
                        {item.label}
                      </div>
                    </Link>
                  ))}
                  <div className="border-t border-gray-100 my-2"></div>
                  {otherItems.map((item) => (
                    <Link
                      key={item.path}
                      to={item.path}
                      className="block px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                      onClick={() => setProfileDropdownOpen(false)}
                    >
                      <div className="flex items-center gap-3">
                        <item.icon className="w-4 h-4 text-gray-400" />
                        {item.label}
                      </div>
                    </Link>
                  ))}
                  <div className="border-t border-gray-100 my-2"></div>
                  <button
                    onClick={handleLogout}
                    className="w-full text-left px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <div className="flex items-center gap-3">
                      <LogOut className="w-4 h-4 text-red-400" />
                      Logout
                    </div>
                  </button>
                </div>
              )}
            </div>
          </div>
        </header>

        {/* Scrollable Body */}
        <main className="flex-1 overflow-y-auto w-full px-4 lg:px-12 pb-12 custom-scrollbar">
          <Outlet />
        </main>
      </div>

      <style>{`
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E0; }
      `}</style>
    </div>
  );
}
