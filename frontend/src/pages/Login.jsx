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
        <div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 md:p-10">
          {/* Logo */}
          <div className="flex justify-center mb-8">
            <Link to="/">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 454.7 110" className="h-12 w-auto">
                <path d="M65.7 53.7L40.3 53.7Q38.1 53.7 36.6 55.25Q35.1 56.8 35.1 58.9L35.1 58.9L35.1 64.2L87.6 64.2L87.6 80L40.1 80L35.1 69.2L35.1 80L17.6 80L17.6 58.1Q17.6 53.6 19.3 49.6Q21 45.6 24 42.6Q27 39.6 31 37.9Q35 36.2 39.5 36.2L39.5 36.2L64.9 36.2Q67.1 36.2 68.6 34.7Q70.1 33.2 70.1 31L70.1 31Q70.1 28.8 68.6 27.3Q67.1 25.8 64.9 25.8L64.9 25.8L17.6 25.8L17.6 10L65.7 10Q70.2 10 74.2 11.7Q78.2 13.4 81.2 16.4Q84.2 19.4 85.9 23.35Q87.6 27.3 87.6 31.8L87.6 31.8Q87.6 36.3 85.9 40.3Q84.2 44.3 81.2 47.3Q78.2 50.3 74.2 52Q70.2 53.7 65.7 53.7L65.7 53.7" fill="#000000"/>
                <path d="M108.1 10L147.5 10Q153.5 10 160 11.35Q166.5 12.7 171.9 16.5Q177.3 20.3 180.8 27.15Q184.3 34 184.3 45L184.3 45Q184.3 52.3 182.7 57.8Q181.1 63.3 178.35 67.15Q175.6 71 171.9 73.5Q168.2 76 164.1 77.45Q160 78.9 155.7 79.45Q151.4 80 147.4 80L147.4 80L131.2 80L125.6 67.6L125.6 80L108.1 80L108.1 10M125.6 64.2L147.3 64.2Q151.1 64.2 154.3 63.4Q157.5 62.6 159.85 60.45Q162.2 58.3 163.5 54.55Q164.8 50.8 164.8 45L164.8 45Q164.8 39.2 163.5 35.45Q162.2 31.7 159.85 29.55Q157.5 27.4 154.3 26.6Q151.1 25.8 147.3 25.8L147.3 25.8L125.6 25.8L125.6 64.2M214.3 10L231.7 10L258 80L240.5 80L236.9 70.4L217.5 70.4L212.8 60.5L205.5 80L188 80L214.3 10M214.8 54.6L231.1 54.6L223 29.2L214.8 54.6M326.2 60.8L343.7 10L361.2 10L334.9 80L314.2 80L307.3 34.2L299.7 80L279.7 80L253.4 10L270.9 10L288.4 60.8L297.5 10L317.1 10L326.2 60.8M420.5 52.3L420.5 10L438 10L438 80L420.5 80L388.4 38.7L388.4 80L370.9 80L370.9 10L388.4 10L388.4 10.1L388.4 10L420.5 52.3" fill="#8B5CF6"/>
              </svg>
            </Link>
          </div>

          {/* Header */}
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-gray-900 mb-2">Sign In</h1>
            <p className="text-gray-500 text-sm">Access your 2DAWN account</p>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-gray-900 placeholder-gray-400"
                placeholder="Enter your email"
                required
              />
            </div>

            <div>
              <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div className="relative">
                <input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-gray-900 placeholder-gray-400 pr-12"
                  placeholder="Enter your password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
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
                <input type="checkbox" className="w-4 h-4 text-[#8b5cf6] border-gray-300 rounded focus:ring-[#8b5cf6]" />
                <span className="ml-2 text-sm text-gray-500 font-medium">Remember me</span>
              </label>
              <Link to="/forgot-password" className="text-sm text-[#8b5cf6] hover:text-[#7c3aed] font-medium">
                Forgot password?
              </Link>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-[#8b5cf6] text-white py-3 rounded-lg font-medium hover:bg-[#7c3aed] disabled:opacity-50 transition shadow-sm"
            >
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-gray-500 text-sm">
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
