import { Link } from 'react-router-dom';
import { useState } from 'react';
import { Instagram } from 'lucide-react';

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
              Tix is an event ticketing platform for memorable experiences in Africa.
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
                <Instagram className="w-5 h-5" />
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
