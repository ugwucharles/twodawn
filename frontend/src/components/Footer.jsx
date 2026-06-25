import { Link } from 'react-router-dom';
import { useState } from 'react';

function Footer() {
  const currentYear = new Date().getFullYear();
  const [email, setEmail] = useState('');

  const handleSubscribe = (e) => {
    e.preventDefault();
    // TODO: Implement newsletter subscription logic
    console.log('Newsletter subscription:', email);
    setEmail('');
  };

  return (
    <footer className="bg-black border-t border-zinc-900 py-12 w-full mt-auto">
      <div className="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10">
          
          {/* Logo & Description */}
          <div className="flex flex-col items-center md:items-start text-center md:text-left">
            <Link to="/" className="flex items-center mb-4">
              <img src="/logo-white2.svg" alt="2DAWN" className="h-8 w-auto opacity-80 hover:opacity-100 transition-opacity" />
            </Link>
            <p className="text-sm text-zinc-400 leading-relaxed max-w-xs">
              2DAWN is your go-to platform for discovering and booking unforgettable events across Nigeria.
            </p>
          </div>

          {/* Newsletter */}
          <div className="flex flex-col items-center md:items-start text-center md:text-left">
            <h3 className="text-sm font-bold text-white mb-3 uppercase tracking-wider">Newsletter</h3>
            <p className="text-xs text-zinc-400 mb-4 max-w-xs">
              Sign up to our newsletter to receive information about upcoming events.
            </p>
            <form onSubmit={handleSubscribe} className="w-full max-w-xs">
              <div className="flex gap-2">
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="Your Email"
                  className="flex-1 px-4 py-2 bg-zinc-900 border border-zinc-800 rounded-lg text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent"
                  required
                />
                <button
                  type="submit"
                  className="px-4 py-2 bg-[#8b5cf6] hover:bg-[#7c3aed] text-white text-sm font-semibold rounded-lg transition-colors duration-200"
                >
                  Subscribe
                </button>
              </div>
            </form>
          </div>

          {/* Navigation & Social */}
          <div className="flex flex-col items-center md:items-start text-center md:text-left">
            <h3 className="text-sm font-bold text-white mb-3 uppercase tracking-wider">Quick Links</h3>
            <nav className="flex flex-col gap-2 text-sm font-semibold text-zinc-400 mb-4">
              <Link to="/" className="hover:text-[#7c3aed] transition-colors duration-200">
                Home
              </Link>
              <Link to="/events" className="hover:text-[#7c3aed] transition-colors duration-200">
                Discover events
              </Link>
              <Link to="/find-tickets" className="hover:text-[#7c3aed] transition-colors duration-200">
                Find my tickets
              </Link>
              <Link to="/organizer/login" className="hover:text-[#7c3aed] transition-colors duration-200">
                Create event
              </Link>
            </nav>
            
            {/* Social Media */}
            <div className="flex items-center gap-4">
              <a
                href="https://instagram.com/2dawn.tix"
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-2 text-zinc-400 hover:text-[#E1306C] transition-colors duration-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
                <span className="text-sm font-semibold">@2dawn.tix</span>
              </a>
            </div>
          </div>

        </div>

        {/* Copyright */}
        <div className="border-t border-zinc-900 pt-8 text-center">
          <div className="text-xs text-zinc-500 font-medium">
            &copy; {currentYear} 2DAWN. All rights reserved.
          </div>
        </div>

      </div>
    </footer>
  );
}

export default Footer;
