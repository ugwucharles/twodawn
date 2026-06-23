import { useEffect, useState } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import { getQuote, createOrder } from '../services/checkout'
import { getEventById } from '../services/events'
import Header from '../components/Header'
import Footer from '../components/Footer'
import { getEventImage } from '../utils/image'
import { formatPrice } from '../utils/price'

function Checkout() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [event, setEvent] = useState(null)
  const [quote, setQuote] = useState(null)
  const [quantity, setQuantity] = useState(1)
  const [ticketType, setTicketType] = useState('')
  const [couponCode, setCouponCode] = useState('')
  const [buyerName, setBuyerName] = useState('')
  const [buyerEmail, setBuyerEmail] = useState('')
  const [buyerPhone, setBuyerPhone] = useState('')
  const [loading, setLoading] = useState(true)
  const [processing, setProcessing] = useState(false)
  const [error, setError] = useState(null)
  const [couponApplied, setCouponApplied] = useState(false)

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
      // Auto-select first ticket type if available
      if (response.event?.ticket_types?.length > 0) {
        setTicketType(response.event.ticket_types[0].name)
      }
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

  const handleApplyCoupon = () => {
    setCouponApplied(true)
    fetchQuote()
  }

  const handleBuyTicket = async () => {
    if (!buyerName.trim()) { setError('Please enter your full name.'); return }
    if (!buyerEmail.trim()) { setError('Please enter your email address.'); return }
    if (!buyerPhone.trim()) { setError('Please enter your phone number.'); return }

    setProcessing(true)
    setError(null)

    try {
      const orderData = {
        buyer_name: buyerName.trim(),
        buyer_email: buyerEmail.trim(),
        buyer_phone: buyerPhone.trim(),
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
      setError('Failed to create order. Please try again.')
      setProcessing(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="flex flex-col items-center gap-4 py-20">
            <div className="w-10 h-10 border-4 border-[#8b5cf6] border-t-transparent rounded-full animate-spin"></div>
            <p className="text-gray-500 text-sm font-medium">Loading checkout...</p>
          </div>
        </main>
      </div>
    )
  }

  if (!event) {
    return (
      <div className="min-h-screen bg-gray-50 flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-20">
            <div className="text-5xl mb-4">😕</div>
            <p className="text-gray-600 font-medium">Event not found</p>
            <Link to="/events" className="mt-4 inline-block text-[#8b5cf6] font-semibold hover:underline">
              ← Back to Events
            </Link>
          </div>
        </main>
      </div>
    )
  }

  const eventImage = getEventImage(event)
  const hasTicketTypes = event.ticket_types && event.ticket_types.length > 0

  const inputClass = "w-full px-4 py-3 border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-[#8b5cf6]/40 focus:border-[#8b5cf6] text-gray-900 placeholder-gray-400 text-sm transition-all duration-200"
  const labelClass = "block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1.5"

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />

      <main className="flex-1 py-8 sm:py-12">
        <div className="max-w-5xl mx-auto px-4 md:px-6 lg:px-10">

          {/* Breadcrumb */}
          <div className="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <Link to="/" className="hover:text-[#8b5cf6] transition-colors">Home</Link>
            <span>/</span>
            <Link to={`/events/${event.id}`} className="hover:text-[#8b5cf6] transition-colors">{event.title}</Link>
            <span>/</span>
            <span className="text-gray-900 font-medium">Checkout</span>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">

            {/* Left: Form */}
            <div className="lg:col-span-3 flex flex-col gap-6">

              {/* Buyer Info */}
              <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 className="text-lg font-extrabold text-gray-900 mb-5">Your Details</h2>
                <div className="flex flex-col gap-4">
                  <div>
                    <label className={labelClass}>Full Name</label>
                    <input
                      type="text"
                      value={buyerName}
                      onChange={(e) => setBuyerName(e.target.value)}
                      className={inputClass}
                      placeholder="e.g. John Doe"
                    />
                  </div>
                  <div>
                    <label className={labelClass}>Email Address</label>
                    <input
                      type="email"
                      value={buyerEmail}
                      onChange={(e) => setBuyerEmail(e.target.value)}
                      className={inputClass}
                      placeholder="e.g. john@example.com"
                    />
                  </div>
                  <div>
                    <label className={labelClass}>Phone Number</label>
                    <input
                      type="tel"
                      value={buyerPhone}
                      onChange={(e) => setBuyerPhone(e.target.value)}
                      className={inputClass}
                      placeholder="e.g. 08012345678"
                    />
                  </div>
                </div>
              </div>

              {/* Ticket Selection */}
              <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 className="text-lg font-extrabold text-gray-900 mb-5">Select Tickets</h2>

                {hasTicketTypes ? (
                  <div className="flex flex-col gap-3 mb-5">
                    {event.ticket_types.map((type, i) => (
                      <button
                        key={i}
                        type="button"
                        onClick={() => setTicketType(type.name)}
                        className={`flex items-center justify-between p-4 rounded-xl border-2 text-left transition-all duration-200 ${
                          ticketType === type.name
                            ? 'border-[#8b5cf6] bg-purple-50'
                            : 'border-gray-100 bg-white hover:border-gray-300'
                        }`}
                      >
                        <div>
                          <div className="flex items-center gap-2">
                            <div className={`w-4 h-4 rounded-full border-2 shrink-0 transition-all ${
                              ticketType === type.name
                                ? 'border-[#8b5cf6] bg-[#8b5cf6]'
                                : 'border-gray-300 bg-white'
                            }`} />
                            <p className="font-bold text-gray-900">{type.name}</p>
                          </div>
                          {type.description && (
                            <p className="text-xs text-gray-500 mt-1 ml-6">{type.description}</p>
                          )}
                        </div>
                        <span className={`font-extrabold text-lg shrink-0 ml-4 ${
                          ticketType === type.name ? 'text-[#8b5cf6]' : 'text-gray-900'
                        }`}>
                          {Number(type.price) === 0 ? 'Free' : `₦${Number(type.price).toLocaleString()}`}
                        </span>
                      </button>
                    ))}
                  </div>
                ) : null}

                {/* Quantity */}
                <div>
                  <label className={labelClass}>Quantity</label>
                  <div className="flex items-center gap-3">
                    <button
                      type="button"
                      onClick={() => setQuantity(q => Math.max(1, q - 1))}
                      className="w-10 h-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-700 hover:border-[#8b5cf6] hover:text-[#8b5cf6] transition-all font-bold text-lg"
                    >
                      −
                    </button>
                    <span className="w-12 text-center font-extrabold text-gray-900 text-xl">{quantity}</span>
                    <button
                      type="button"
                      onClick={() => setQuantity(q => Math.min(event.capacity || 10, q + 1))}
                      className="w-10 h-10 rounded-xl border border-gray-200 bg-white flex items-center justify-center text-gray-700 hover:border-[#8b5cf6] hover:text-[#8b5cf6] transition-all font-bold text-lg"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>

              {/* Coupon */}
              <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 className="text-lg font-extrabold text-gray-900 mb-4">Have a Coupon?</h2>
                <div className="flex gap-2">
                  <input
                    type="text"
                    value={couponCode}
                    onChange={(e) => { setCouponCode(e.target.value); setCouponApplied(false) }}
                    className={`${inputClass} flex-1`}
                    placeholder="Enter coupon code"
                  />
                  <button
                    type="button"
                    onClick={handleApplyCoupon}
                    className="px-5 py-3 rounded-xl bg-gray-900 text-white text-sm font-bold hover:bg-gray-700 transition-colors whitespace-nowrap"
                  >
                    Apply
                  </button>
                </div>
                {couponApplied && quote?.discount > 0 && (
                  <p className="mt-2 text-sm text-green-600 font-semibold">✓ Coupon applied! You saved ₦{quote.discount.toFixed(2)}</p>
                )}
              </div>

              {error && (
                <div className="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-medium">
                  {error}
                </div>
              )}
            </div>

            {/* Right: Order Summary + CTA */}
            <div className="lg:col-span-2">
              <div className="sticky top-8 flex flex-col gap-4">

                {/* Event Card */}
                <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                  <div className="h-36 bg-gradient-to-br from-purple-100 to-purple-50 relative">
                    {eventImage ? (
                      <img src={eventImage} alt={event.title} className="absolute inset-0 w-full h-full object-cover" />
                    ) : (
                      <div className="absolute inset-0 flex items-center justify-center">
                        <span className="text-4xl">🎟️</span>
                      </div>
                    )}
                  </div>
                  <div className="p-4">
                    <h3 className="font-extrabold text-gray-900 text-base leading-tight">{event.title}</h3>
                    {event.venue && (
                      <p className="text-xs text-gray-500 mt-1 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        {event.venue}
                      </p>
                    )}
                    {event.starts_at && (
                      <p className="text-xs text-gray-500 mt-1 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {new Date(event.starts_at).toLocaleDateString('en-NG', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </p>
                    )}
                  </div>
                </div>

                {/* Order Summary */}
                {quote && (
                  <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 className="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Order Summary</h3>
                    <div className="flex flex-col gap-2 text-sm">
                      <div className="flex justify-between">
                        <span className="text-gray-600">Subtotal</span>
                        <span className="font-semibold text-gray-900">₦{(quote.subtotal || 0).toFixed(2)}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600">Service Fee</span>
                        <span className="font-semibold text-gray-900">₦{(quote.fee || 0).toFixed(2)}</span>
                      </div>
                      {quote.discount > 0 && (
                        <div className="flex justify-between text-green-600">
                          <span>Discount</span>
                          <span className="font-semibold">-₦{(quote.discount || 0).toFixed(2)}</span>
                        </div>
                      )}
                      <div className="flex justify-between items-center text-base font-extrabold text-gray-900 border-t border-gray-100 pt-3 mt-1">
                        <span>Total</span>
                        <span className="text-[#8b5cf6]">₦{(quote.total || 0).toFixed(2)}</span>
                      </div>
                    </div>
                  </div>
                )}

                {/* Pay Button */}
                <button
                  type="button"
                  onClick={handleBuyTicket}
                  disabled={processing}
                  className="w-full bg-[#8b5cf6] hover:bg-[#7c3aed] disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-4 rounded-2xl text-base font-extrabold shadow-lg shadow-purple-200 hover:shadow-purple-300 transition-all duration-200 active:scale-95"
                >
                  {processing ? (
                    <span className="flex items-center justify-center gap-2">
                      <svg className="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                      </svg>
                      Processing...
                    </span>
                  ) : (
                    `Pay ${quote ? `₦${(quote.total || 0).toFixed(2)}` : 'Now'}`
                  )}
                </button>
                <p className="text-center text-xs text-gray-400">🔒 Secure checkout powered by Paystack</p>

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
