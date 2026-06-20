import { Link } from 'react-router-dom';

function Footer() {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-black border-t border-zinc-900 py-10 w-full mt-auto">
      <div className="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">
        <div className="flex flex-col items-center text-center gap-6">
          
          {/* Logo */}
          <Link to="/" className="flex items-center">
            <img src="/logo.svg" alt="2DAWN" className="h-8 w-auto opacity-80 hover:opacity-100 transition-opacity" />
          </Link>

          {/* Navigation Links */}
          <nav className="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm font-semibold text-zinc-400">
            <Link to="/" className="hover:text-[#7c3aed] transition-colors duration-200">
              Home
            </Link>
            <Link to="/events" className="hover:text-[#7c3aed] transition-colors duration-200">
              Discover events
            </Link>
            <Link to="/events/recent" className="hover:text-[#7c3aed] transition-colors duration-200">
              Find my tickets
            </Link>
            <Link to="/organizer/login" className="hover:text-[#7c3aed] transition-colors duration-200">
              Create event
            </Link>
          </nav>

          {/* Copyright */}
          <div className="text-xs text-zinc-500 font-medium">
            &copy; {currentYear} 2DAWN. All rights reserved.
          </div>

        </div>
      </div>
    </footer>
  );
}

export default Footer;
