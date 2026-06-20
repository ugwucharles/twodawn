import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import axios from 'axios'

function AdminDashboard() {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchDashboard()
  }, [])

  const fetchDashboard = async () => {
    try {
      const response = await axios.get('/admin/dashboard')
      setStats(response.data)
      setLoading(false)
    } catch (err) {
      console.error('Failed to load dashboard', err)
      setLoading(false)
    }
  }

  if (loading) {
    return <div className="text-center py-12">Loading dashboard...</div>
  }

  return (
    <div className="min-h-screen bg-zinc-900">
      <nav className="bg-black shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex items-center">
              <Link to="/" className="text-2xl font-bold text-white">
                2DAWN Admin
              </Link>
            </div>
            <div className="flex items-center space-x-4">
              <Link to="/admin/events" className="text-gray-300 hover:text-white">
                Events
              </Link>
              <Link to="/admin/orders" className="text-gray-300 hover:text-white">
                Orders
              </Link>
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 className="text-3xl font-bold text-white mb-8">Dashboard</h1>

        {stats && (
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div className="bg-zinc-800 rounded-lg shadow-md p-6">
              <h3 className="text-gray-400 text-sm font-medium">Total Events</h3>
              <p className="text-3xl font-bold text-white mt-2">
                {stats.stats?.events_total || 0}
              </p>
            </div>
            <div className="bg-zinc-800 rounded-lg shadow-md p-6">
              <h3 className="text-gray-400 text-sm font-medium">Published Events</h3>
              <p className="text-3xl font-bold text-white mt-2">
                {stats.stats?.events_published || 0}
              </p>
            </div>
            <div className="bg-zinc-800 rounded-lg shadow-md p-6">
              <h3 className="text-gray-400 text-sm font-medium">Orders Today</h3>
              <p className="text-3xl font-bold text-white mt-2">
                {stats.stats?.orders_today || 0}
              </p>
            </div>
            <div className="bg-zinc-800 rounded-lg shadow-md p-6">
              <h3 className="text-gray-400 text-sm font-medium">Revenue Today</h3>
              <p className="text-3xl font-bold text-white mt-2">
                ₦{((stats.stats?.revenue_today || 0) / 100).toFixed(2)}
              </p>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}

export default AdminDashboard
