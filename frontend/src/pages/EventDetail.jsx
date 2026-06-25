import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { getEventById } from '../services/events'
import Header from '../components/Header'
import Footer from '../components/Footer'
import { getEventImage } from '../utils/image'
import { formatPrice } from '../utils/price'

function EventDetail() {
  const { id } = useParams()
  const [event, setEvent] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    fetchEvent()
  }, [id])

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

  const formatDate = (dateStr) => {
    if (!dateStr) return 'TBD'
    const d = new Date(dateStr)
    return d.toLocaleDateString('en-NG', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  }

  const formatTime = (dateStr) => {
    if (!dateStr) return ''
    const d = new Date(dateStr)
    return d.toLocaleTimeString('en-NG', { hour: '2-digit', minute: '2-digit' })
  }

  const shareOnTwitter = () => {
    if (!event) return
    const url = encodeURIComponent(window.location.href)
    const text = encodeURIComponent(`Check out "${event.title}" on 2Dawn!`)
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank')
  }

  const shareOnFacebook = () => {
    if (!event) return
    const url = encodeURIComponent(window.location.href)
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank')
  }

  const copyLink = () => {
    navigator.clipboard.writeText(window.location.href)
      .then(() => alert('Link copied!'))
      .catch(() => {})
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="flex flex-col items-center gap-4 py-20">
            <div className="w-10 h-10 border-4 border-[#8b5cf6] border-t-transparent rounded-full animate-spin"></div>
            <p className="text-gray-500 text-sm font-medium">Loading event...</p>
          </div>
        </main>
      </div>
    )
  }

  if (error || !event) {
    return (
      <div className="min-h-screen bg-gray-50 flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-20">
            <div className="text-5xl mb-4">😕</div>
            <p className="text-gray-600 font-medium">{error || 'Event not found'}</p>
            <Link to="/events" className="mt-4 inline-block text-[#8b5cf6] font-semibold hover:underline">
              ← Back to Events
            </Link>
          </div>
        </main>
      </div>
    )
  }

  const eventImage = getEventImage(event)
  const isSoldOut = event.capacity !== null && event.capacity <= 0
  const isEnded = event.ends_at ? new Date(event.ends_at) < new Date() : false
  const hasTicketTypes = event.ticket_types && event.ticket_types.length > 0

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Header />

      <main className="flex-1 py-8 sm:py-12">
        <div className="max-w-6xl mx-auto px-4 md:px-6 lg:px-10">

          {/* Breadcrumb */}
          <div className="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <Link to="/" className="hover:text-[#8b5cf6] transition-colors">Home</Link>
            <span>/</span>
            <Link to="/events" className="hover:text-[#8b5cf6] transition-colors">Events</Link>
            <span>/</span>
            <span className="text-gray-900 font-medium line-clamp-1">{event.title}</span>
          </div>

          {/* Main two-column layout */}
          <div className="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10">

            {/* Left: Event Flyer */}
            <div className="lg:col-span-2">
              <div className="sticky top-8">
                <div className="relative rounded-2xl overflow-hidden shadow-lg bg-gradient-to-br from-purple-100 to-purple-50 aspect-[3/4]">
                  {eventImage ? (
                    <img
                      src={eventImage}
                      alt={event.title}
                      className="absolute inset-0 w-full h-full object-cover"
                    />
                  ) : (
                    <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-gradient-to-br from-[#8b5cf6]/10 to-purple-100">
                      <div className="w-16 h-16 rounded-full bg-[#8b5cf6]/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8 text-[#8b5cf6]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                      </div>
                      <span className="text-[#8b5cf6] font-semibold text-sm">No Image Available</span>
                    </div>
                  )}
                  {isEnded && (
                    <div className="absolute inset-0 bg-black/60 flex items-center justify-center z-10">
                      <span className="text-white font-extrabold text-lg uppercase tracking-widest px-4 py-2 rounded-lg bg-gray-700">
                        Sales Closed
                      </span>
                    </div>
                  )}
                  {isSoldOut && !isEnded && (
                    <div className="absolute inset-0 bg-black/60 flex items-center justify-center z-10">
                      <span className="text-white font-extrabold text-lg uppercase tracking-widest px-4 py-2 rounded-lg bg-red-600">
                        Sold Out
                      </span>
                    </div>
                  )}
                </div>

                {/* Share Section */}
                <div className="mt-6">
                  <p className="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">Share this event</p>
                  <div className="flex items-center gap-3">
                    <button
                      onClick={shareOnTwitter}
                      className="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:border-[#8b5cf6] hover:text-[#8b5cf6] transition-all duration-200"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.74l7.73-8.835L1.254 2.25H8.08l4.261 5.635 5.903-5.635zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                      </svg>
                      Twitter
                    </button>
                    <button
                      onClick={shareOnFacebook}
                      className="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:border-[#8b5cf6] hover:text-[#8b5cf6] transition-all duration-200"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                      </svg>
                      Facebook
                    </button>
                    <button
                      onClick={copyLink}
                      title="Copy link"
                      className="flex items-center justify-center w-10 h-10 rounded-xl border border-gray-200 bg-white text-gray-600 hover:border-[#8b5cf6] hover:text-[#8b5cf6] transition-all duration-200 shrink-0"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {/* Right: Event Details */}
            <div className="lg:col-span-3 flex flex-col gap-6">

              {/* Title & Organizer */}
              <div>
                <h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight mb-3">
                  {event.title}
                </h1>
                <div className="flex items-center gap-2">
                  {event.organizer_profile_picture ? (
                    <img
                      src={event.organizer_profile_picture}
                      alt={event.organizer_name || event.organizer_username || 'Organizer'}
                      className="w-8 h-8 rounded-full object-cover shrink-0"
                    />
                  ) : (
                    <div className="w-8 h-8 rounded-full bg-gradient-to-br from-[#8b5cf6] to-purple-400 flex items-center justify-center text-white font-bold text-sm shrink-0">
                      {(event.organizer_name || event.organizer_username || '?')[0].toUpperCase()}
                    </div>
                  )}
                  <div>
                    <span className="text-sm text-gray-500">Hosted by </span>
                    {event.organizer_name ? (
                      <>
                        <span className="text-sm font-bold text-gray-900">{event.organizer_name}</span>
                        {event.organizer_username && (
                          <span className="text-sm text-gray-400 ml-1">(@{event.organizer_username})</span>
                        )}
                      </>
                    ) : event.organizer_username ? (
                      <span className="text-sm font-bold text-gray-900">@{event.organizer_username}</span>
                    ) : (
                      <span className="text-sm text-gray-400 italic">Unknown organizer</span>
                    )}
                  </div>
                </div>
              </div>

              {/* Info Cards */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {/* Date */}
                <div className="flex items-start gap-4 p-4 rounded-2xl bg-white border border-gray-100 shadow-sm">
                  <div className="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-[#8b5cf6]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <p className="text-xs font-bold uppercase tracking-widest text-gray-400">Date & Time</p>
                    <p className="mt-0.5 text-sm font-bold text-gray-900">{formatDate(event.starts_at)}</p>
                    {event.starts_at && (
                      <p className="text-sm text-gray-500">{formatTime(event.starts_at)}</p>
                    )}
                  </div>
                </div>

                {/* Venue */}
                <div className="flex items-start gap-4 p-4 rounded-2xl bg-white border border-gray-100 shadow-sm">
                  <div className="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-[#8b5cf6]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0" />
                    </svg>
                  </div>
                  <div>
                    <p className="text-xs font-bold uppercase tracking-widest text-gray-400">Venue</p>
                    <p className="mt-0.5 text-sm font-bold text-gray-900">{event.venue || 'TBD'}</p>
                  </div>
                </div>
              </div>

              {/* Ticket Types */}
              {hasTicketTypes ? (
                <div className="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                  <h2 className="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Tickets</h2>
                  <div className="flex flex-col gap-3">
                    {event.ticket_types.map((type, i) => (
                      <div key={i} className="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                        <div>
                          <p className="font-bold text-gray-900">{type.name}</p>
                          {type.description && (
                            <p className="text-xs text-gray-500 mt-0.5">{type.description}</p>
                          )}
                        </div>
                        <span className="font-extrabold text-[#8b5cf6] text-lg">
                          {Number(type.price) === 0 ? 'Free' : `₦${Number(type.price).toLocaleString()}`}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              ) : (
                <div className="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Ticket Price</p>
                      <p className="text-3xl font-extrabold text-[#8b5cf6]">{formatPrice(event)}</p>
                    </div>
                    <div className="text-4xl">🎟️</div>
                  </div>
                </div>
              )}

              {/* CTA Button */}
              <div>
                {isEnded ? (
                  <button
                    disabled
                    className="w-full bg-gray-300 text-gray-500 py-4 rounded-2xl text-lg font-bold cursor-not-allowed"
                  >
                    Sales Closed
                  </button>
                ) : isSoldOut ? (
                  <button
                    disabled
                    className="w-full bg-gray-300 text-gray-500 py-4 rounded-2xl text-lg font-bold cursor-not-allowed"
                  >
                    Sold Out
                  </button>
                ) : (
                  <Link
                    to={`/events/${event.id}/checkout`}
                    className="block w-full bg-[#8b5cf6] hover:bg-[#7c3aed] text-white py-4 rounded-2xl text-lg font-extrabold text-center shadow-lg shadow-purple-200 hover:shadow-purple-300 transition-all duration-200 active:scale-95"
                  >
                    Get Tickets
                  </Link>
                )}
                <p className="text-center text-xs text-gray-400 mt-2">Secure checkout powered by Paystack</p>
              </div>

              {/* Description / Must Know */}
              {event.description && (
                <div className="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                  <h2 className="text-sm font-bold uppercase tracking-widest text-gray-400 mb-3">About this Event</h2>
                  <div
                    className="text-gray-700 text-sm leading-relaxed prose prose-sm max-w-none"
                    dangerouslySetInnerHTML={{ __html: event.description }}
                  />
                </div>
              )}

            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  )
}

export default EventDetail
