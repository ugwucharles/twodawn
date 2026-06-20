import { useState } from 'react'
import { Link } from 'react-router-dom'

function Header() {
  const [isOpen, setIsOpen] = useState(false)

  return (
    <header className="bg-black border-b border-zinc-800 sticky top-0 z-[100] h-[72px] w-full flex items-center">
      <div className="w-full px-4 md:px-6 lg:px-10 h-full flex items-center justify-between gap-4">
        
        {/* Left: Logo */}
        <div className="flex items-center shrink-0">
          <Link to="/" className="flex items-center">
            <img src="/logo.svg" alt="2DAWN" className="h-10 w-auto" />
          </Link>
        </div>

        {/* Right: Desktop Navigation */}
        <div className="hidden lg:flex items-center gap-1 shrink-0">
          <nav className="flex items-center text-[16px] font-medium text-zinc-300">
            <Link to="/events" className="px-3.5 py-2 hover:text-[#7c3aed] transition-colors">Discover events</Link>
            <Link to="/events/recent" className="px-3.5 py-2 hover:text-[#7c3aed] transition-colors">Find my tickets</Link>
            <Link to="/organizer/login" className="px-3.5 py-2 hover:text-[#7c3aed] transition-colors">Create event</Link>
          </nav>
        </div>

        {/* Mobile Nav Controls */}
        <div className="flex lg:hidden items-center gap-2">
          <button 
            type="button" 
            className="p-2 text-zinc-300 hover:text-white rounded-full transition-colors" 
            aria-label="Open menu" 
            onClick={() => setIsOpen(true)}
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
              <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>
      </div>

      {/* Overlay */}
      {isOpen && (
        <div 
          className="fixed inset-0 bg-black/80 backdrop-blur-xl z-[110] lg:hidden" 
          onClick={() => setIsOpen(false)}
          aria-hidden="true"
        />
      )}

      {/* Mobile Drawer */}
      {isOpen && (
        <aside className="fixed inset-x-0 top-0 w-full bg-black border-b border-zinc-800 shadow-2xl z-[120] flex flex-col lg:hidden max-h-screen overflow-y-auto">
          
          {/* Drawer Header */}
          <div className="flex items-center justify-between p-4 border-b border-zinc-800">
            <Link to="/" className="flex items-center" onClick={() => setIsOpen(false)}>
              <img src="/logo.svg" alt="2DAWN" className="h-10 w-auto" />
            </Link>
            <button 
              type="button" 
              className="p-2 text-zinc-400 hover:bg-zinc-800 hover:text-white rounded-full transition-colors" 
              aria-label="Close menu" 
              onClick={() => setIsOpen(false)}
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd"/>
              </svg>
            </button>
          </div>
          
          {/* Drawer Content */}
          <div className="flex-1 overflow-y-auto py-2 px-0">
            <nav className="grid text-[16px] text-zinc-300">
              <Link to="/events" className="px-6 py-4 hover:bg-zinc-900 hover:text-white transition-colors border-b border-zinc-800" onClick={() => setIsOpen(false)}>Discover events</Link>
              <Link to="/events/recent" className="px-6 py-4 hover:bg-zinc-900 hover:text-white transition-colors border-b border-zinc-800" onClick={() => setIsOpen(false)}>Find my tickets</Link>
              <Link to="/organizer/login" className="px-6 py-4 hover:bg-zinc-900 hover:text-white transition-colors border-b border-zinc-800" onClick={() => setIsOpen(false)}>Create event</Link>
            </nav>
          </div>
        </aside>
      )}
    </header>
  )
}

export default Header
