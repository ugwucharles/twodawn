import { useEffect, useState } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import { getQuote, createOrder } from '../services/checkout'
import { getEventById } from '../services/events'
import Header from '../components/Header'
import Footer from '../components/Footer'

function Checkout() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [event, setEvent] = useState(null)
  const [quote, setQuote] = useState(null)
  const [quantity, setQuantity] = useState(1)
  const [ticketType, setTicketType] = useState('')
  const [couponCode, setCouponCode] = useState('')
  const [loading, setLoading] = useState(true)
  const [processing, setProcessing] = useState(false)
  const [error, setError] = useState(null)

  useEffect(() => {
    fetchEvent()
  }, [id])

  useEffect(() => {
    if (event) {
      fetchQuote()
    }
  }, [event, quantity, ticketType, couponCode])

  const fetchEvent = async () => {
    try {
      const response = await getEventById(id)
      setEvent(response.event)
      setLoading(false)
    } catch (err) {
      setError('Failed to load event')
      setLoading(false)
    }
  }

  const fetchQuote = async () => {
    try {
      const response = await getQuote(id, { quantity, ticket_type: ticketType, coupon_code: couponCode })
      setQuote(response)
    } catch (err) {
      console.error('Failed to fetch quote', err)
    }
  }

  const handleBuyTicket = async () => {
    setProcessing(true)
    setError(null)

    try {
      const orderData = {
        buyer_name: '',
        buyer_email: '',
        buyer_phone: '',
        quantity,
        ticket_type: ticketType,
        coupon_code: couponCode,
      }

      const response = await createOrder(id, orderData)
      
      if (response.authorization_url) {
        window.location.href = response.authorization_url
      } else if (response.order) {
        navigate(`/orders/${response.order.paystack_reference}`)
      }
    } catch (err) {
      setError('Failed to create order')
      setProcessing(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12">Loading checkout...</div>
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

  if (!event) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12 text-gray-600">Event not found</div>
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
              <h1 className="text-3xl font-bold text-gray-900 mb-6">{event.title}</h1>
              
              <div className="space-y-6">
                <div>
                  <label className="block text-gray-700 text-sm font-bold mb-2">
                    Quantity
                  </label>
                  <input
                    type="number"
                    min="1"
                    max={event.capacity || 10}
                    value={quantity}
                    onChange={(e) => setQuantity(parseInt(e.target.value))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6]"
                  />
                </div>

                {event.ticket_types && event.ticket_types.length > 0 && (
                  <div>
                    <label className="block text-gray-700 text-sm font-bold mb-2">
                      Ticket Type
                    </label>
                    <select
                      value={ticketType}
                      onChange={(e) => setTicketType(e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6]"
                    >
                      <option value="">Select ticket type</option>
                      {event.ticket_types.map((type) => (
                        <option key={type.name} value={type.name}>
                          {type.name} - ₦{type.price}
                        </option>
                      ))}
                    </select>
                  </div>
                )}

                <div>
                  <label className="block text-gray-700 text-sm font-bold mb-2">
                    Coupon Code (optional)
                  </label>
                  <input
                    type="text"
                    value={couponCode}
                    onChange={(e) => setCouponCode(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8b5cf6]"
                    placeholder="Enter coupon code"
                  />
                </div>

                {quote && (
                  <div className="bg-gray-50 rounded-lg p-6">
                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    <div className="space-y-2">
                      <div className="flex justify-between">
                        <span className="text-gray-600">Subtotal:</span>
                        <span className="font-medium">₦{quote.subtotal?.toFixed(2) || '0.00'}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Fee:</span>
                        <span className="font-medium">₦{quote.fee?.toFixed(2) || '0.00'}</span>
                      </div>
                      {quote.discount > 0 && (
                        <div className="flex justify-between text-green-600">
                          <span>Discount:</span>
                          <span className="font-medium">-₦{quote.discount?.toFixed(2)}</span>
                        </div>
                      )}
                      <div className="flex justify-between text-lg font-bold border-t pt-2">
                        <span>Total:</span>
                        <span>₦{quote.total?.toFixed(2) || '0.00'}</span>
                      </div>
                    </div>
                  </div>
                )}

                <button
                  onClick={handleBuyTicket}
                  disabled={processing || !quote}
                  className="w-full bg-[#8b5cf6] text-white py-3 rounded-lg font-medium hover:bg-[#7c3aed] disabled:opacity-50"
                >
                  {processing ? 'Processing...' : 'Buy Ticket'}
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

export default Checkout
