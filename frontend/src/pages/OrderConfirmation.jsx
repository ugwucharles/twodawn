import { useEffect, useState } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { getOrder } from '../services/checkout'
import Header from '../components/Header'
import Footer from '../components/Footer'
import { QRCodeSVG } from 'qrcode.react'

function OrderConfirmation() {
  const { reference } = useParams()
  const navigate = useNavigate()
  const [order, setOrder] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    fetchOrder()
  }, [reference])

  const fetchOrder = async () => {
    try {
      const response = await getOrder(reference)
      setOrder(response.order)
      setLoading(false)
    } catch (err) {
      setError('Failed to load order')
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12">Loading order...</div>
        </main>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12 text-red-600">{error}</div>
        </main>
      </div>
    )
  }

  if (!order) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12 text-gray-600">Order not found</div>
        </main>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <Header />
      
      <main className="flex-1 pt-8 sm:pt-10 pb-16">
        <div className="max-w-4xl mx-auto px-4 md:px-6 lg:px-10">
          <div className="bg-white rounded-lg shadow-md overflow-hidden">
            <div className="p-8">
              {order.status === 'paid' ? (
                <div className="text-center">
                  <div className="text-green-600 text-6xl mb-4">✓</div>
                  <h1 className="text-3xl font-bold text-gray-900 mb-4">Payment Successful!</h1>
                  <p className="text-gray-600 mb-8">
                    Your ticket has been confirmed. Check your email for details.
                  </p>
                </div>
              ) : (
                <div className="text-center">
                  <div className="text-yellow-600 text-6xl mb-4">⏳</div>
                  <h1 className="text-3xl font-bold text-gray-900 mb-4">Payment Pending</h1>
                  <p className="text-gray-600 mb-8">
                    Your payment is being processed. You will receive an email confirmation shortly.
                  </p>
                </div>
              )}

              <div className="bg-gray-50 rounded-lg p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Order Details</h3>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-gray-600">Reference:</span>
                    <span className="font-medium">{order.paystack_reference}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Ticket Code:</span>
                    <span className="font-medium">{order.ticket_code}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Quantity:</span>
                    <span className="font-medium">{order.quantity}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Amount:</span>
                    <span className="font-medium">₦{(order.amount / 100).toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-gray-600">Status:</span>
                    <span className={`font-medium ${order.status === 'paid' ? 'text-green-600' : 'text-yellow-600'}`}>
                      {order.status}
                    </span>
                  </div>
                </div>
              </div>

              {order.status === 'paid' && (
                <div className="bg-gray-50 rounded-lg p-6 mt-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4 text-center">Your Ticket QR Code</h3>
                  <div className="flex justify-center">
                    <QRCodeSVG
                      value={order.ticket_code || order.paystack_reference}
                      size={200}
                      level="H"
                      includeMargin={true}
                    />
                  </div>
                  <p className="text-center text-sm text-gray-500 mt-4">
                    Show this QR code at the event entrance for check-in
                  </p>
                </div>
              )}

              <div className="mt-8 text-center">
                <button
                  onClick={() => navigate('/')}
                  className="bg-[#8b5cf6] text-white px-8 py-3 rounded-lg font-medium hover:bg-[#7c3aed]"
                >
                  Back to Home
                </button>
              </div>
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  )
}

export default OrderConfirmation
