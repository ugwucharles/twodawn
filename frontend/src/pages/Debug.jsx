import { useEffect, useState } from 'react'
import { getEvents, getTopSellingEvents } from '../services/events'

function Debug() {
  const [logs, setLogs] = useState([])
  const [loading, setLoading] = useState(true)

  const addLog = (message, data = null) => {
    setLogs(prev => [...prev, { time: new Date().toISOString(), message, data }])
  }

  useEffect(() => {
    const testApi = async () => {
      addLog('Starting API test...')
      
      try {
        addLog('Fetching events from /api/v1/events...')
        const eventsResponse = await getEvents()
        addLog('Events response received', eventsResponse)
        addLog(`Events count: ${eventsResponse.events?.length || 0}`)
      } catch (err) {
        addLog('Events fetch ERROR', {
          message: err.message,
          response: err.response?.data,
          status: err.response?.status
        })
      }

      try {
        addLog('Fetching top selling events from /api/v1/events/top-selling...')
        const topSellingResponse = await getTopSellingEvents(6)
        addLog('Top selling response received', topSellingResponse)
        addLog(`Top selling count: ${topSellingResponse.events?.length || 0}`)
      } catch (err) {
        addLog('Top selling fetch ERROR', {
          message: err.message,
          response: err.response?.data,
          status: err.response?.status
        })
      }

      setLoading(false)
    }

    testApi()
  }, [])

  return (
    <div className="min-h-screen bg-gray-900 text-white p-8">
      <div className="max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold mb-6">API Debug Page</h1>
        
        {loading && (
          <div className="text-yellow-400 mb-4">Testing API endpoints...</div>
        )}

        <div className="space-y-4">
          {logs.map((log, index) => (
            <div key={index} className="bg-gray-800 rounded-lg p-4">
              <div className="text-gray-400 text-sm mb-2">{log.time}</div>
              <div className={`font-semibold ${log.message.includes('ERROR') ? 'text-red-400' : 'text-green-400'}`}>
                {log.message}
              </div>
              {log.data && (
                <pre className="mt-2 bg-gray-900 p-3 rounded text-xs overflow-auto">
                  {JSON.stringify(log.data, null, 2)}
                </pre>
              )}
            </div>
          ))}
        </div>

        <div className="mt-8 p-4 bg-gray-800 rounded-lg">
          <h2 className="text-xl font-bold mb-2">API Configuration</h2>
          <p>API Base URL: {import.meta.env.VITE_API_URL || 'https://twodawn-frontend.vercel.app'}</p>
          <p>Environment: {import.meta.env.DEV ? 'Development' : 'Production'}</p>
        </div>
      </div>
    </div>
  )
}

export default Debug
