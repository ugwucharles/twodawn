import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import axios from 'axios'

function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)

    try {
      const response = await axios.post('/organizer/login', { email, password })
      // Store token and redirect
      localStorage.setItem('token', response.data.token)
      navigate('/organizer/dashboard')
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid credentials')
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#8b5cf6]/30 flex items-center justify-center p-4" style={{ fontFamily: 'Montserrat, sans-serif' }}>
      <div className="w-full max-w-md">
        <div className="bg-black rounded-2xl shadow-xl p-8 md:p-10">
          {/* Logo */}
          <div className="flex justify-center mb-8">
            <Link to="/">
              <img src="/logo.svg" alt="2DAWN" className="h-12 w-auto" />
            </Link>
          </div>

          {/* Header */}
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-white mb-2">Sign In</h1>
            <p className="text-gray-400 text-sm">Access your 2DAWN account</p>
          </div>

          {error && (
            <div className="bg-red-900/30 border border-red-800 text-red-300 px-4 py-3 rounded-lg mb-6 text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-300 mb-2">
                Email
              </label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-white placeholder-gray-500"
                placeholder="Enter your email"
                required
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-300 mb-2">
                Password
              </label>
              <div className="relative">
                <input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-white placeholder-gray-500 pr-12"
                  placeholder="Enter your password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-300"
                >
                  {showPassword ? (
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  ) : (
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  )}
                </button>
              </div>
            </div>

            <div className="flex items-center justify-between">
              <label className="flex items-center">
                <input type="checkbox" className="w-4 h-4 text-[#8b5cf6] bg-gray-900 border-gray-700 rounded focus:ring-[#8b5cf6]" />
                <span className="ml-2 text-sm text-gray-400">Remember me</span>
              </label>
              <Link to="/forgot-password" className="text-sm text-[#8b5cf6] hover:text-[#7c3aed]">
                Forgot password?
              </Link>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-[#8b5cf6] text-white py-3 rounded-lg font-medium hover:bg-[#7c3aed] disabled:opacity-50 transition"
            >
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-gray-400 text-sm">
              Don't have an account?{' '}
              <Link to="/register" className="text-[#8b5cf6] hover:text-[#7c3aed] font-medium">
                Sign up
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Login
