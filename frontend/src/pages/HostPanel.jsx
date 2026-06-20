import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import axios from 'axios'

function HostPanel() {
  const { token } = useParams()
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    fetchHostData()
  }, [token])

  const fetchHostData = async () => {
    try {
      const response = await axios.get(`/h/${token}`)
      setData(response.data)
      setLoading(false)
    } catch (err) {
      setError('Invalid or expired host link')
      setLoading(false)
    }
  }

  if (loading) {
    return <div className="text-center py-12">Loading host panel...</div>
  }

  if (error) {
    return <div className="text-center py-12 text-red-600">{error}</div>
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex items-center">
              <span className="text-2xl font-bold text-indigo-600">
                2DAWN Host Panel
              </span>
            </div>
          </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 className="text-3xl font-bold text-gray-900 mb-8">
          {data?.event?.title}
        </h1>
        
        {data?.stats && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div className="bg-white rounded-lg shadow-md p-6">
              <h3 className="text-gray-600 text-sm font-medium">Tickets Sold</h3>
              <p className="text-3xl font-bold text-gray-900 mt-2">
                {data.stats.sold || 0}
              </p>
            </div>
            <div className="bg-white rounded-lg shadow-md p-6">
              <h3 className="text-gray-600 text-sm font-medium">Checked In</h3>
              <p className="text-3xl font-bold text-gray-900 mt-2">
                {data.stats.checked || 0}
              </p>
            </div>
            <div className="bg-white rounded-lg shadow-md p-6">
              <h3 className="text-gray-600 text-sm font-medium">Remaining</h3>
              <p className="text-3xl font-bold text-gray-900 mt-2">
                {data.stats.remaining || 0}
              </p>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}

export default HostPanel
