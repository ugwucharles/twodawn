import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../services/api'

function Onboarding() {
  const [username, setUsername] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const navigate = useNavigate()

  const handleUsernameChange = (e) => {
    const val = e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '')
    setUsername(val)
    setError('')
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!username.trim()) {
      setError('Please enter a username.')
      return
    }

    setLoading(true)
    setError('')

    try {
      const response = await api.post('/organizer/onboarding', { username })
      if (response.data.ok) {
        // Update user state if context exists, or just redirect
        navigate(response.data.redirect || '/organizer/dashboard')
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to save username. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-zinc-50 flex items-center justify-center p-4" style={{ fontFamily: 'Montserrat, sans-serif' }}>
      <div className="w-full max-w-md">
        {/* Logo */}
        <div className="flex justify-center mb-8">
          <img src="/logo-auth.svg" alt="2DAWN" className="h-8 w-auto" />
        </div>

        {/* Card */}
        <div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 md:p-10">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-gray-900 mb-2">Claim your handle</h1>
            <p className="text-gray-500 text-sm">
              Choose an organizer username to display on your event detail pages.
            </p>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label htmlFor="username" className="block text-sm font-medium text-gray-700 mb-2">
                Organizer Username
              </label>
              <div className="flex rounded-lg shadow-sm">
                <span className="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm font-medium">
                  twodawn.com.ng/
                </span>
                <input
                  id="username"
                  type="text"
                  value={username}
                  onChange={handleUsernameChange}
                  className="w-full min-w-0 px-4 py-3 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-gray-900 placeholder-gray-400 font-semibold"
                  placeholder="your-brand"
                  required
                  disabled={loading}
                />
              </div>
              <p className="mt-2 text-xs text-gray-400">
                Only lowercase letters, numbers, and dashes are allowed.
              </p>
            </div>

            <button
              type="submit"
              disabled={loading || !username.trim()}
              className="w-full bg-[#8b5cf6] text-white py-3 rounded-lg font-medium hover:bg-[#7c3aed] disabled:opacity-50 transition shadow-sm"
            >
              {loading ? 'Setting up...' : 'Create Account'}
            </button>
          </form>
        </div>
      </div>
    </div>
  )
}

export default Onboarding
