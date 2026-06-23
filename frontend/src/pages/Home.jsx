import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { getEvents } from '../services/events'
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
      const response = await getEvents()
      setEvents(response.events || [])
      setLoading(false)
    } catch (err) {
      console.error('Failed to load events', err)
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
                  <Link key={event.id} to={`/events/${event.id}`} className="group">
                    <div className="bg-white rounded-2xl overflow-hidden shadow-[0_2px_10px_-3px_rgba(0,0,0,0.08)] border border-gray-100 hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.12)] transition-all duration-300">
                      {event.image_path ? (
                        <div className="aspect-[4/3] bg-gray-100 overflow-hidden">
                          <img 
                            src={getEventImage(event)} 
                            alt={event.title}
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                          />
                        </div>
                      ) : (
                        <div className="aspect-[4/3] bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center">
                          <span className="text-4xl font-bold text-purple-200">{event.title.charAt(0)}</span>
                        </div>
                      )}
                      <div className="p-5">
                        <h3 className="font-bold text-gray-900 text-lg mb-2 line-clamp-2">{event.title}</h3>
                        <p className="text-xs text-purple-600 font-semibold mb-2">by {event.organizer_name || event.organizer_username || 'Organizer'}</p>
                        <p className="text-sm text-gray-500 mb-3">{event.venue}</p>
                        <div className="flex items-center justify-between">
                          <span className="text-sm font-semibold text-gray-900">
                            {formatPrice(event)}
                          </span>
                          <span className="text-xs text-gray-400">
                            {new Date(event.starts_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                          </span>
                        </div>
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
