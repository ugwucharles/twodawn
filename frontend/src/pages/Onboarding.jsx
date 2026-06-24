import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../services/api'

function Onboarding() {
  const [username, setUsername] = useState('')
  const [name, setName] = useState('')
  const [instagramHandle, setInstagramHandle] = useState('')
  const [twitterHandle, setTwitterHandle] = useState('')
  const [whatsappNumber, setWhatsappNumber] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [successMsg, setSuccessMsg] = useState('')
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
    setSuccessMsg('')

    try {
      const response = await api.post('/organizer/onboarding', {
        username,
        name: name.trim() || null,
        instagramHandle: instagramHandle.trim() || null,
        twitterHandle: twitterHandle.trim() || null,
        whatsappNumber: whatsappNumber.trim() || null,
      })

      if (response.data.ok) {
        setSuccessMsg('Account created successfully! Redirecting in 2 seconds...')
        setTimeout(() => {
          navigate(response.data.redirect || '/organizer/dashboard')
        }, 2000)
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to save onboarding details. Please try again.')
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-zinc-50 flex items-center justify-center p-4 py-12" style={{ fontFamily: 'Montserrat, sans-serif' }}>
      <div className="w-full max-w-lg">
        {/* Logo */}
        <div className="flex justify-center mb-8">
          <img src="/logo-white2.svg" alt="2DAWN" className="h-8 w-auto" />
        </div>

        {/* Card */}
        <div className="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 md:p-10">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-gray-900 mb-2">Claim your handle</h1>
            <p className="text-gray-500 text-sm">
              Complete your profile details. Your username forms your unique profile URL and displays on your event pages.
            </p>
          </div>

          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
              {error}
            </div>
          )}

          {successMsg && (
            <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm font-semibold flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-green-600 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7" />
              </svg>
              {successMsg}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            {/* Username field */}
            <div>
              <label htmlFor="username" className="block text-sm font-bold text-gray-700 mb-2">
                Organizer Username / Handle <span className="text-red-500">*</span>
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
                  disabled={loading || successMsg}
                />
              </div>
              <p className="mt-2 text-xs text-gray-400">
                Only lowercase letters, numbers, and dashes are allowed. This is permanent.
              </p>
            </div>

            {/* Display Name field */}
            <div>
              <label htmlFor="name" className="block text-sm font-bold text-gray-700 mb-2">
                Brand / Display Name (Optional)
              </label>
              <input
                id="name"
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-gray-900 placeholder-gray-400 font-semibold"
                placeholder="e.g. 2DAWN Events"
                disabled={loading || successMsg}
              />
            </div>

            {/* Social handles section */}
            <div className="border-t border-gray-100 pt-5">
              <h3 className="text-sm font-bold text-gray-800 mb-4">Add your contact info (Optional)</h3>
              
              <div className="space-y-4">
                {/* Instagram */}
                <div>
                  <label htmlFor="instagram" className="block text-xs font-bold text-gray-500 mb-1">
                    Instagram Username
                  </label>
                  <div className="flex rounded-lg shadow-sm">
                    <span className="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                      instagram.com/
                    </span>
                    <input
                      id="instagram"
                      type="text"
                      value={instagramHandle}
                      onChange={(e) => setInstagramHandle(e.target.value)}
                      className="w-full min-w-0 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-gray-900 placeholder-gray-400"
                      placeholder="username"
                      disabled={loading || successMsg}
                    />
                  </div>
                </div>

                {/* Twitter */}
                <div>
                  <label htmlFor="twitter" className="block text-xs font-bold text-gray-500 mb-1">
                    Twitter Handle
                  </label>
                  <div className="flex rounded-lg shadow-sm">
                    <span className="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                      x.com/
                    </span>
                    <input
                      id="twitter"
                      type="text"
                      value={twitterHandle}
                      onChange={(e) => setTwitterHandle(e.target.value)}
                      className="w-full min-w-0 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-gray-900 placeholder-gray-400"
                      placeholder="username"
                      disabled={loading || successMsg}
                    />
                  </div>
                </div>

                {/* WhatsApp */}
                <div>
                  <label htmlFor="whatsapp" className="block text-xs font-bold text-gray-500 mb-1">
                    WhatsApp Number
                  </label>
                  <input
                    id="whatsapp"
                    type="tel"
                    value={whatsappNumber}
                    onChange={(e) => setWhatsappNumber(e.target.value)}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent transition text-gray-900 placeholder-gray-400"
                    placeholder="e.g. +2348012345678"
                    disabled={loading || successMsg}
                  />
                </div>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading || !username.trim() || successMsg}
              className="w-full bg-[#8b5cf6] text-white py-3 rounded-lg font-medium hover:bg-[#7c3aed] disabled:opacity-50 transition shadow-sm mt-6"
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
