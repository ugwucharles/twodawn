import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { getEvents, getTopSellingEvents } from '../services/events'
import Header from '../components/Header'
import Footer from '../components/Footer'
import { formatPrice } from '../utils/price'
import { getEventImage } from '../utils/image'

function Home() {
  const [showLocation, setShowLocation] = useState(false)
  const [showType, setShowType] = useState(false)
  const [showPrice, setShowPrice] = useState(false)
  const [showDate, setShowDate] = useState(false)
  const [locationLabel, setLocationLabel] = useState('Lagos')
  const [typeLabel, setTypeLabel] = useState('All events')
  const [events, setEvents] = useState([])
  const [topSellingEvents, setTopSellingEvents] = useState([])
  const [loading, setLoading] = useState(true)

  const ngStates = {
    'abia': 'Abia',
    'adamawa': 'Adamawa',
    'akwa-ibom': 'Akwa Ibom',
    'anambra': 'Anambra',
    'bauchi': 'Bauchi',
    'bayelsa': 'Bayelsa',
    'benue': 'Benue',
    'borno': 'Borno',
    'cross-river': 'Cross River',
    'delta': 'Delta',
    'ebonyi': 'Ebonyi',
    'edo': 'Edo',
    'ekiti': 'Ekiti',
    'enugu': 'Enugu',
    'gombe': 'Gombe',
    'imo': 'Imo',
    'jigawa': 'Jigawa',
    'kaduna': 'Kaduna',
    'kano': 'Kano',
    'katsina': 'Katsina',
    'kebbi': 'Kebbi',
    'kogi': 'Kogi',
    'kwara': 'Kwara',
    'lagos': 'Lagos',
    'nasarawa': 'Nasarawa',
    'niger': 'Niger',
    'ogun': 'Ogun',
    'ondo': 'Ondo',
    'osun': 'Osun',
    'oyo': 'Oyo',
    'plateau': 'Plateau',
    'rivers': 'Rivers',
    'sokoto': 'Sokoto',
    'taraba': 'Taraba',
    'yobe': 'Yobe',
    'zamfara': 'Zamfara',
    'abuja': 'Abuja (FCT)',
  }

  useEffect(() => {
    fetchEvents()
  }, [])

  const fetchEvents = async () => {
    try {
      console.log('Fetching events from API...')
      const response = await getEvents()
      console.log('Events response:', response)
      setEvents(response.events || [])
      
      console.log('Fetching top selling events...')
      const topSellingResponse = await getTopSellingEvents(6)
      console.log('Top selling response:', topSellingResponse)
      setTopSellingEvents(topSellingResponse.events || [])
      
      setLoading(false)
    } catch (err) {
      console.error('Failed to load events', err)
      console.error('Error details:', err.response?.data || err.message)
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <Header />
      
      <main className="flex-1">
        <section className="relative bg-white pt-8 md:pt-10 pb-16">
          <div className="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">

            {/* Top: location selector */}
            <div className="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8 relative">
              <div className="relative">
                <p className="text-xs font-semibold tracking-[0.18em] text-gray-400 uppercase">
                  Find an event in
                </p>
                <button 
                  type="button"
                  onClick={() => { setShowLocation(!showLocation); setShowType(false); setShowPrice(false); setShowDate(false) }}
                  className="mt-2 inline-flex items-center px-4 py-2 rounded-full border border-gray-100 bg-white text-[15px] font-semibold text-gray-900 shadow-sm hover:border-gray-400 hover:shadow-md transition duration-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0"/>
                  </svg>
                  <span className="ml-2 mr-1">{locationLabel}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" className={`h-4 w-4 text-gray-400 ml-1 transition-transform duration-250 ease-out ${showLocation ? 'rotate-180 text-purple-600' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.2">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                
                {showLocation && (
                  <div className="absolute z-20 mt-2 max-h-[320px] w-64 overflow-y-auto rounded-2xl border border-purple-100/80 bg-white/95 backdrop-blur-xl shadow-[0_12px_30px_-4px_rgba(0,0,0,0.08)] py-2 px-1.5 text-sm text-gray-900 animate-dropdown">
                    {Object.entries(ngStates).map(([code, label]) => (
                      <button
                        key={code}
                        type="button"
                        className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold"
                        onClick={() => { setLocationLabel(label); setShowLocation(false) }}
                      >
                        {label}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>

            {/* Top Selling Events */}
            {topSellingEvents.length > 0 && (
              <div className="mb-8">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-lg font-bold text-gray-900">Top Selling Events</h2>
                </div>
                <div className="flex gap-4 overflow-x-auto pb-4 scrollbar-hide">
                  {topSellingEvents.map((event) => (
                    <Link
                      key={event.id}
                      to={`/events/${event.id}`}
                      className="group shrink-0 w-56"
                    >
                      <div className="relative aspect-[3/4] rounded-xl overflow-hidden bg-gray-100">
                        {getEventImage(event) ? (
                          <img
                            src={getEventImage(event)}
                            alt={event.title}
                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                          />
                        ) : (
                          <div className="w-full h-full bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center">
                            <span className="text-3xl">🎟️</span>
                          </div>
                        )}
                        {/* Dark overlay - darker at bottom for text readability */}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-transparent" />
                        {/* Text inside card */}
                        <div className="absolute bottom-0 left-0 right-0 p-4">
                          <h3 className="text-base font-bold text-white line-clamp-2 mb-1">
                            {event.title}
                          </h3>
                          <p className="text-sm text-white/90">{formatPrice(event)}</p>
                        </div>
                        {event.capacity !== null && event.capacity <= 0 && (
                          <div className="absolute inset-0 bg-black/60 flex items-center justify-center z-10">
                            <span className="text-white font-extrabold text-xs uppercase tracking-widest px-2 py-1 rounded bg-red-600">Sold Out</span>
                          </div>
                        )}
                      </div>
                    </Link>
                  ))}
                </div>
              </div>
            )}

            {/* Filters row + Popular events heading */}
            <div className="flex flex-col gap-4 mb-6">
              <div className="flex flex-wrap items-center gap-3 relative">
                <div className="relative">
                  <button 
                    type="button"
                    onClick={() => { setShowType(!showType); setShowPrice(false); setShowDate(false) }}
                    className="inline-flex items-center px-4 py-2 rounded-full border border-gray-100 bg-white text-sm font-semibold text-gray-900 hover:border-gray-400 hover:shadow-sm transition duration-200"
                  >
                    <span>{typeLabel}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" className={`h-4 w-4 ml-2 text-gray-400 transition-transform duration-250 ease-out ${showType ? 'rotate-180 text-purple-600' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                  {showType && (
                    <div className="absolute z-20 mt-2 w-48 rounded-2xl border border-purple-100/80 bg-white/95 backdrop-blur-xl shadow-[0_12px_30px_-4px_rgba(0,0,0,0.08)] py-2 px-1.5 text-sm text-gray-900 animate-dropdown">
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => { setTypeLabel('All events'); setShowType(false) }}>All events</button>
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => { setTypeLabel('Online events'); setShowType(false) }}>Online events</button>
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => { setTypeLabel('In-person events'); setShowType(false) }}>In-person events</button>
                    </div>
                  )}
                </div>
                <div className="relative">
                  <button 
                    type="button"
                    onClick={() => { setShowPrice(!showPrice); setShowDate(false) }}
                    className="inline-flex items-center px-4 py-2 rounded-full border border-gray-100 bg-white text-sm font-semibold text-gray-900 hover:border-gray-400 hover:shadow-sm transition duration-200"
                  >
                    Price
                    <svg xmlns="http://www.w3.org/2000/svg" className={`h-4 w-4 ml-2 text-gray-400 transition-transform duration-250 ease-out ${showPrice ? 'rotate-180 text-purple-600' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                  {showPrice && (
                    <div className="absolute z-20 mt-2 w-44 rounded-2xl border border-purple-100/80 bg-white/95 backdrop-blur-xl shadow-[0_12px_30px_-4px_rgba(0,0,0,0.08)] py-2 px-1.5 text-sm text-gray-900 animate-dropdown">
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => setShowPrice(false)}>Any price</button>
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => setShowPrice(false)}>Free</button>
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => setShowPrice(false)}>Paid</button>
                    </div>
                  )}
                </div>
                <div className="relative">
                  <button 
                    type="button"
                    onClick={() => { setShowDate(!showDate); setShowPrice(false) }}
                    className="inline-flex items-center px-4 py-2 rounded-full border border-gray-100 bg-white text-sm font-semibold text-gray-900 hover:border-gray-400 hover:shadow-sm transition duration-200"
                  >
                    Date
                    <svg xmlns="http://www.w3.org/2000/svg" className={`h-4 w-4 ml-2 text-gray-400 transition-transform duration-250 ease-out ${showDate ? 'rotate-180 text-purple-600' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                      <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                  {showDate && (
                    <div className="absolute z-20 mt-2 w-52 rounded-2xl border border-purple-100/80 bg-white/95 backdrop-blur-xl shadow-[0_12px_30px_-4px_rgba(0,0,0,0.08)] py-2 px-1.5 text-sm text-gray-900 animate-dropdown">
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => setShowDate(false)}>Any date</button>
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => setShowDate(false)}>Today</button>
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => setShowDate(false)}>This weekend</button>
                      <button type="button" className="w-full text-left px-3.5 py-2 hover:bg-purple-50 hover:text-purple-600 rounded-xl transition-all duration-150 font-semibold" onClick={() => setShowDate(false)}>Next week</button>
                    </div>
                  )}
                </div>
              </div>
              <div className="flex items-center justify-between">
                <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">
                  Popular events
                </h2>
                <Link to="/events" className="text-sm font-medium text-gray-500 hover:text-[#8b5cf6] transition-colors">
                  See all
                </Link>
              </div>
            </div>

            {/* Popular events list (Compact Cards) */}
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              {loading ? (
                <div className="py-20 text-center text-gray-400 col-span-full">
                  Loading events...
                </div>
              ) : events.length === 0 ? (
                <div className="py-20 text-center text-gray-400 col-span-full">
                  No matching events found.
                </div>
              ) : (
                events.map((event) => (
                  <Link
                    key={event.id}
                    to={`/events/${event.id}`}
                    className="flex items-center rounded-2xl border border-purple-200 bg-white hover:shadow-xl hover:border-purple-300 transition-all duration-300 overflow-hidden group h-44"
                  >
                    {/* Left: Image */}
                    <div className="w-32 sm:w-40 h-full relative bg-gray-50 shrink-0">
                      {getEventImage(event) ? (
                        <img 
                          src={getEventImage(event)} 
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
                              <span className="truncate">
                                {new Date(event.starts_at).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}
                              </span>
                            </div>
                          )}
                          <div className="mt-1 text-[11px] text-purple-600 font-bold">
                            by {event.organizer_name || event.organizer_username || 'Organizer'}
                          </div>
                        </div>
                        {event.description && (
                          <div className="mt-2 text-[12px] text-gray-400 line-clamp-2 leading-relaxed">
                            {event.description.replace(/<[^>]*>/g, '').substring(0, 100)}...
                          </div>
                        )}
                      </div>
                      <div>
                        <span className="text-[15px] font-black text-[#8b5cf6]">
                          {formatPrice(event)}
                        </span>
                      </div>
                    </div>
                  </Link>
                ))
              )}
            </div>

          </div>
        </section>
      </main>
      <Footer />
    </div>
  )
}

export default Home
