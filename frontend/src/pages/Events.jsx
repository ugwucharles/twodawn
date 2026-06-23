import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getEvents, getRecentEvents } from '../services/events'
import Header from '../components/Header'
import Footer from '../components/Footer'

function Events({ recent = false }) {
  const [events, setEvents] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    fetchEvents()
  }, [recent])

  const fetchEvents = async () => {
    try {
      setLoading(true)
      const data = recent ? await getRecentEvents() : await getEvents()
      setEvents(data.events || [])
      setLoading(false)
    } catch (err) {
      setError('Failed to load events')
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12">Loading events...</div>
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

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <Header />
      
      <main className="flex-1 pt-8 sm:pt-10 pb-16">
        <div className="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">
          <h1 className="text-3xl font-bold text-gray-900 mb-8">
            {recent ? 'Recent events' : 'Discover events'}
          </h1>
          
          {events.length === 0 ? (
            <div className="text-center py-12 text-gray-600">
              No events available at the moment
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              {events.map((event) => (
                <Link
                  key={event.id}
                  to={`/events/${event.id}`}
                  className="flex items-center rounded-2xl border border-gray-100 bg-white hover:shadow-xl hover:border-gray-200 transition-all duration-300 overflow-hidden group h-44"
                >
                  {/* Left: Image */}
                  <div className="w-32 sm:w-40 h-full relative bg-gray-50 shrink-0">
                    {event.image_url ? (
                      <img 
                        src={event.image_url} 
                        alt={event.title}
                        className="absolute inset-0 h-full w-full object-cover group-hover:scale-110 transition-transform duration-700"
                      />
                    ) : (
                      <div className="absolute inset-0 h-full w-full bg-gradient-to-br from-indigo-100 to-indigo-200"></div>
                    )}
                    {event.capacity !== null && event.capacity <= 0 ? (
                      <div className="absolute inset-0 bg-black/60 flex items-center justify-center z-10">
                        <span className="text-white font-extrabold text-[10px] uppercase tracking-widest px-2 py-0.5 rounded bg-red-600">Sold Out</span>
                      </div>
                    ) : (
                      <div className="absolute inset-0 bg-black/5"></div>
                    )}
                  </div>
                  {/* Right: Details */}
                  <div className="flex-1 p-3 sm:p-4 flex flex-col justify-between min-w-0 h-full">
                    <div>
                      <h3 className="text-sm sm:text-[15px] font-bold text-gray-900 line-clamp-2 transition-colors group-hover:text-[#8b5cf6] leading-snug">
                        {event.title}
                      </h3>
                      <div className="mt-1 text-[13px] text-gray-500 font-medium">
                        {event.starts_at && (
                          <div className="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span className="truncate">{new Date(event.starts_at).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}</span>
                          </div>
                        )}
                        <div className="mt-1 text-[11px] text-purple-600 font-bold">
                          by {event.organizer_name || event.organizer_username || 'Organizer'}
                        </div>
                      </div>
                      {event.description && (
                        <div className="mt-2 text-[12px] text-gray-400 line-clamp-2 leading-relaxed">
                          {event.description.substring(0, 100)}...
                        </div>
                      )}
                    </div>
                    <div>
                      <span className="text-[15px] font-black text-[#8b5cf6]">
                        {event.price && event.price > 0 ? `₦${event.price.toLocaleString()}` : 'Free'}
                      </span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </div>
      </main>
      <Footer />
    </div>
  )
}

export default Events
