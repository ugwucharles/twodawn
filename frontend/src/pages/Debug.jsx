import { useEffect, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { getEvents, getTopSellingEvents } from '../services/events'
import { getOrder } from '../services/checkout'

function Debug() {
  const [logs, setLogs] = useState([])
  const [loading, setLoading] = useState(true)
  const [mounted, setMounted] = useState(false)
  const [searchParams] = useSearchParams()

  const addLog = (message, data = null) => {
    setLogs(prev => [...prev, { time: new Date().toISOString(), message, data }])
  }

  useEffect(() => {
    setMounted(true)
    const testApi = async () => {
      addLog('Starting Debug Page...')
      
      // Check URL parameters for payment reference
      const reference = searchParams.get('reference')
      if (reference) {
        addLog('Payment reference found in URL', { reference })
        
        try {
          addLog('Fetching order details...')
          const orderResponse = await getOrder(reference)
          addLog('Order response received', orderResponse)
          addLog(`Order status: ${orderResponse.order?.status}`)
          addLog(`Order amount: ${orderResponse.order?.amount}`)
        } catch (err) {
          addLog('Order fetch ERROR', {
            message: err.message,
            response: err.response?.data,
            status: err.response?.status
          })
        }
      } else {
        addLog('No payment reference in URL')
      }

      // Check for payment errors in localStorage
      const paymentError = localStorage.getItem('payment_error')
      if (paymentError) {
        addLog('Payment Error (from localStorage)', JSON.parse(paymentError))
        localStorage.removeItem('payment_error')
      }

      // Check for payment state in localStorage
      const paymentState = localStorage.getItem('payment_state')
      if (paymentState) {
        addLog('Payment State (from localStorage)', JSON.parse(paymentState))
      }

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

      // Check for Google sign-in errors from localStorage
      const googleError = localStorage.getItem('google_signin_error')
      if (googleError) {
        addLog('Google Sign-in Error (from localStorage)', JSON.parse(googleError))
        localStorage.removeItem('google_signin_error')
      }

      setLoading(false)
    }

    testApi()
  }, [searchParams])

  if (!mounted) return <div>Loading...</div>

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#1a1a1a', color: 'white', padding: '20px' }}>
      <div style={{ maxWidth: '800px', margin: '0 auto' }}>
        <h1 style={{ fontSize: '32px', fontWeight: 'bold', marginBottom: '20px' }}>API Debug Page</h1>
        
        {loading && (
          <div style={{ color: '#fbbf24', marginBottom: '20px' }}>Testing API endpoints...</div>
        )}

        <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
          {logs.map((log, index) => (
            <div key={index} style={{ backgroundColor: '#2d2d2d', borderRadius: '8px', padding: '16px' }}>
              <div style={{ color: '#9ca3af', fontSize: '14px', marginBottom: '8px' }}>{log.time}</div>
              <div style={{ fontWeight: 'bold', color: log.message.includes('ERROR') ? '#f87171' : '#4ade80' }}>
                {log.message}
              </div>
              {log.data && (
                <pre style={{ marginTop: '8px', backgroundColor: '#1a1a1a', padding: '12px', borderRadius: '4px', fontSize: '12px', overflow: 'auto' }}>
                  {JSON.stringify(log.data, null, 2)}
                </pre>
              )}
            </div>
          ))}
        </div>

        <div style={{ marginTop: '32px', padding: '16px', backgroundColor: '#2d2d2d', borderRadius: '8px' }}>
          <h2 style={{ fontSize: '20px', fontWeight: 'bold', marginBottom: '8px' }}>API Configuration</h2>
          <p>API Base URL: {import.meta.env.VITE_API_URL || 'https://twodawn-frontend.vercel.app'}</p>
          <p>Environment: {import.meta.env.DEV ? 'Development' : 'Production'}</p>
        </div>
      </div>
    </div>
  )
}

export default Debug
