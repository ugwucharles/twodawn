import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import useAuthStore from '../store/authStore'
import { 
  LayoutGrid, 
  Calendar, 
  ShoppingBag, 
  Scan, 
  Wallet, 
  Settings, 
  LogOut,
  ChevronDown
} from 'lucide-react'

function Header() {
  const [isOpen, setIsOpen] = useState(false)
  const [profileDropdownOpen, setProfileDropdownOpen] = useState(false)
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated)
  const logout = useAuthStore((state) => state.logout)
  const navigate = useNavigate()

  const handleLogout = () => {
    logout()
    setProfileDropdownOpen(false)
    setIsOpen(false)
    navigate('/')
  }

  const navItems = [
    { path: '/organizer/dashboard', label: 'Dashboard', icon: LayoutGrid },
    { path: '/organizer/events', label: 'Events', icon: Calendar },
    { path: '/organizer/orders', label: 'Orders', icon: ShoppingBag },
    { path: '/organizer/scanner', label: 'Scanner', icon: Scan },
  ]

  const otherItems = [
    { path: '/organizer/wallet', label: 'Wallet', icon: Wallet },
    { path: '/organizer/settings', label: 'Settings', icon: Settings },
  ]

  return (
    <header className="bg-black border-b border-zinc-800 sticky top-0 z-[100] h-[72px] w-full flex items-center">
      {(isOpen || profileDropdownOpen) && (
        <div 
          className="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 cursor-default" 
          onClick={() => {
            setIsOpen(false)
            setProfileDropdownOpen(false)
          }}
        />
      )}
      <div className="w-full px-4 md:px-6 lg:px-10 h-full flex items-center justify-between gap-4 relative z-50">
        
        {/* Left: Logo */}
        <div className="flex items-center shrink-0">
          <Link to="/" className="flex items-center">
            <img src="/logo-white2.svg" alt="2DAWN" className="h-10 w-auto" />
          </Link>
        </div>

        {/* Center: Desktop Navigation */}
        <nav className="hidden lg:flex items-center text-[15px] font-light tracking-wide text-zinc-300">
          <Link to="/events" className="px-3.5 py-2 hover:text-[#7c3aed] transition-colors font-light">Discover events</Link>
          <Link to="/find-tickets" className="px-3.5 py-2 hover:text-[#7c3aed] transition-colors font-light">Find my tickets</Link>
          {!isAuthenticated && (
            <Link to="/organizer/login" className="px-3.5 py-2 hover:text-[#7c3aed] transition-colors font-light">Create event</Link>
          )}
        </nav>

        {/* Right: Desktop Actions */}
        <div className="hidden lg:flex items-center gap-4 shrink-0">
          {!isAuthenticated && (
            <Link 
              to="/organizer/login" 
              className="px-5 py-2.5 bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-sm font-medium rounded-lg transition-colors"
            >
              Sign in
            </Link>
          )}

          {isAuthenticated && (
            <div className="relative">
              <button 
                type="button" 
                className="p-2 text-zinc-300 hover:text-white rounded-full transition-colors" 
                aria-label="Open menu" 
                onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
              </button>

              {/* Dropdown Menu */}
              {profileDropdownOpen && (
                <div className="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 text-gray-900">
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
          )}
        </div>

        {/* Mobile Nav Controls */}
        <div className="flex lg:hidden items-center gap-2">
          <div className="relative">
            <button 
              type="button" 
              className="p-2 text-zinc-300 hover:text-white rounded-full transition-colors" 
              aria-label="Open menu" 
              onClick={() => setIsOpen(!isOpen)}
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
            </button>

            {/* Dropdown Menu */}
            {isOpen && (
              <div className="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-[130] text-gray-900">
                <Link
                  to="/events"
                  className="block px-4 py-3 text-sm font-light tracking-wide text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                  onClick={() => setIsOpen(false)}
                >
                  Discover events
                </Link>
                <Link
                  to="/find-tickets"
                  className="block px-4 py-3 text-sm font-light tracking-wide text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                  onClick={() => setIsOpen(false)}
                >
                  Find my tickets
                </Link>

                {!isAuthenticated ? (
                  <>
                    <Link
                      to="/organizer/login"
                      className="block px-4 py-3 text-sm font-light tracking-wide text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors border-t border-gray-100 mt-1 pt-3"
                      onClick={() => setIsOpen(false)}
                    >
                      Create event
                    </Link>
                    <Link
                      to="/organizer/login"
                      className="block px-4 py-3 text-sm font-medium text-[#7c3aed] hover:bg-purple-50 transition-colors"
                      onClick={() => setIsOpen(false)}
                    >
                      Sign in
                    </Link>
                  </>
                ) : (
                  <>
                    <div className="border-t border-gray-100 my-2"></div>
                    {navItems.map((item) => (
                      <Link
                        key={item.path}
                        to={item.path}
                        className="block px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                        onClick={() => setIsOpen(false)}
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
                        onClick={() => setIsOpen(false)}
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
                  </>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  )
}

export default Header
