import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../../services/api'
import AuthLogo from '../../components/AuthLogo'

function AdminLogin() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const handleSubmit = async (event) => {
    event.preventDefault()
    setError('')
    setLoading(true)

    try {
      const response = await api.post('/xyz/login', { email, password })
      localStorage.setItem('token', response.data.token)
      navigate(response.data.redirect || '/ucc/dashboard')
    } catch (err) {
      setError(err.response?.data?.message || 'Admin sign in failed')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gray-950 flex items-center justify-center p-4" style={{ fontFamily: 'Montserrat, sans-serif' }}>
      <div className="w-full max-w-md">
        <div className="mb-8 flex justify-center">
          <AuthLogo />
        </div>

        <div className="bg-gray-900 rounded-2xl shadow-xl border border-gray-800 p-8 md:p-10">
          <div className="text-center mb-8">
            <p className="text-xs uppercase tracking-[0.24em] text-purple-400 font-semibold mb-3">Command Center</p>
            <h1 className="text-2xl font-bold text-white mb-2">Admin Sign In</h1>
            <p className="text-gray-400 text-sm">Access the 2DAWN control room</p>
          </div>

          {error && (
            <div className="bg-red-500/10 border border-red-500/30 text-red-200 px-4 py-3 rounded-lg mb-6 text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label htmlFor="admin-email" className="block text-sm font-medium text-gray-200 mb-2">
                Email
              </label>
              <input
                id="admin-email"
                type="email"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                className="w-full px-4 py-3 bg-gray-950 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-white placeholder-gray-500"
                placeholder="Admin email"
                required
              />
            </div>

            <div>
              <label htmlFor="admin-password" className="block text-sm font-medium text-gray-200 mb-2">
                Password
              </label>
              <div className="relative">
                <input
                  id="admin-password"
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(event) => setPassword(event.target.value)}
                  className="w-full px-4 py-3 bg-gray-950 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-white placeholder-gray-500 pr-20"
                  placeholder="Admin password"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400 hover:text-white"
                >
                  {showPassword ? 'Hide' : 'Show'}
                </button>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-500 disabled:opacity-50 transition shadow-sm"
            >
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}

export default AdminLogin
