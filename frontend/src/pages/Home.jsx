import { useState, useEffect, useRef } from 'react'
import { ChevronDown } from 'lucide-react'
import { Link } from 'react-router-dom'
import { getEvents, getTopSellingEvents, getRecentEvents } from '../services/events'
import Header from '../components/Header'
import Footer from '../components/Footer'
import { formatPrice } from '../utils/price'
import { getEventImage } from '../utils/image'

function Home() {
  const [events, setEvents] = useState([])
  const [topSellingEvents, setTopSellingEvents] = useState([])
  const [recentEvents, setRecentEvents] = useState([])
  const [topSellingState, setTopSellingState] = useState('')
  const [stateDropdownOpen, setStateDropdownOpen] = useState(false)
  const [priceFilter, setPriceFilter] = useState('')
  const [priceDropdownOpen, setPriceDropdownOpen] = useState(false)
  const [dateFilter, setDateFilter] = useState('')
  const [dateDropdownOpen, setDateDropdownOpen] = useState(false)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const dropdownRef = useRef(null)
  const priceDropdownRef = useRef(null)
  const dateDropdownRef = useRef(null)

  const stateOptions = [
    { value: '', label: 'All states' },
    { value: 'lagos', label: 'Lagos' },
    { value: 'abuja', label: 'Abuja (FCT)' },
    { value: 'rivers', label: 'Rivers' },
    { value: 'oyo', label: 'Oyo' },
    { value: 'kano', label: 'Kano' },
    { value: 'akwa-ibom', label: 'Akwa Ibom' },
    { value: 'delta', label: 'Delta' },
    { value: 'anambra', label: 'Anambra' },
    { value: 'edo', label: 'Edo' },
    { value: 'enugu', label: 'Enugu' },
    { value: 'ogun', label: 'Ogun' },
    { value: 'plateau', label: 'Plateau' },
    { value: 'kaduna', label: 'Kaduna' },
    { value: 'kwara', label: 'Kwara' },
    { value: 'imo', label: 'Imo' },
    { value: 'niger', label: 'Niger' },
    { value: 'osun', label: 'Osun' },
    { value: 'bayelsa', label: 'Bayelsa' },
    { value: 'katsina', label: 'Katsina' },
    { value: 'cross-river', label: 'Cross River' },
    { value: 'jigawa', label: 'Jigawa' },
    { value: 'adamawa', label: 'Adamawa' },
    { value: 'bauchi', label: 'Bauchi' },
    { value: 'borno', label: 'Borno' },
    { value: 'ebonyi', label: 'Ebonyi' },
    { value: 'ekiti', label: 'Ekiti' },
    { value: 'gombe', label: 'Gombe' },
    { value: 'kebbi', label: 'Kebbi' },
    { value: 'kogi', label: 'Kogi' },
    { value: 'nasarawa', label: 'Nasarawa' },
    { value: 'ondo', label: 'Ondo' },
    { value: 'sokoto', label: 'Sokoto' },
    { value: 'taraba', label: 'Taraba' },
    { value: 'yobe', label: 'Yobe' },
    { value: 'zamfara', label: 'Zamfara' },
    { value: 'benue', label: 'Benue' },
    { value: 'abia', label: 'Abia' },
    { value: 'sokoto', label: 'Sokoto' }
  ]

  const priceOptions = [
    { value: '', label: 'All prices' },
    { value: 'free', label: 'Free' },
    { value: '0-5000', label: 'Under ₦5,000' },
    { value: '5000-10000', label: '₦5,000 - ₦10,000' },
    { value: '10000-20000', label: '₦10,000 - ₦20,000' },
    { value: '20000-50000', label: '₦20,000 - ₦50,000' },
    { value: '50000+', label: '₦50,000+' }
  ]

  const dateOptions = [
    { value: '', label: 'All dates' },
    { value: 'today', label: 'Today' },
    { value: 'tomorrow', label: 'Tomorrow' },
    { value: 'this-week', label: 'This week' },
    { value: 'this-weekend', label: 'This weekend' },
    { value: 'next-week', label: 'Next week' },
    { value: 'this-month', label: 'This month' }
  ]

  useEffect(() => {
    fetchEvents()
    fetchRecentEvents()
  }, [])

  useEffect(() => {
    function handleClickOutside(event) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setStateDropdownOpen(false)
      }
      if (priceDropdownRef.current && !priceDropdownRef.current.contains(event.target)) {
        setPriceDropdownOpen(false)
      }
      if (dateDropdownRef.current && !dateDropdownRef.current.contains(event.target)) {
        setDateDropdownOpen(false)
      }
    }

    if (stateDropdownOpen || priceDropdownOpen || dateDropdownOpen) {
      document.addEventListener('mousedown', handleClickOutside)
      return () => document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [stateDropdownOpen, priceDropdownOpen, dateDropdownOpen])

  useEffect(() => {
    fetchTopSelling(topSellingState)
  }, [topSellingState])

  const fetchEvents = async () => {
    setLoading(true)
    setError(null)

    try {
      const response = await getEvents({})
      setEvents(response.events || [])
    } catch (err) {
      console.error('Failed to load events', err)
      setEvents([])
      setError('Failed to load events')
    } finally {
      setLoading(false)
    }
  }

  const fetchTopSelling = async (state = '') => {
    try {
      const filters = state ? { state } : {}
      const topSellingResponse = await getTopSellingEvents(6, filters)
      setTopSellingEvents(topSellingResponse.events || [])
    } catch (err) {
      console.error('Failed to load top selling', err)
    }
  }

  const fetchRecentEvents = async () => {
    try {
      const response = await getRecentEvents(6)
      setRecentEvents(response.events || [])
    } catch (err) {
      console.error('Failed to load recent events', err)
    }
  }

  const filterEvents = (eventsToFilter) => {
    return eventsToFilter.filter(event => {
      // Price filter
      if (priceFilter) {
        // Check ticket types for pricing
        const ticketTypes = event.ticket_types || []
        const hasFreeTicket = ticketTypes.some(t => t.price === 0 || t.price === '0')
        const prices = ticketTypes.map(t => parseFloat(t.price) || 0)

        if (priceFilter === 'free' && !hasFreeTicket) return false
        if (priceFilter === '0-5000' && !prices.some(p => p > 0 && p <= 5000)) return false
        if (priceFilter === '5000-10000' && !prices.some(p => p > 5000 && p <= 10000)) return false
        if (priceFilter === '10000-20000' && !prices.some(p => p > 10000 && p <= 20000)) return false
        if (priceFilter === '20000-50000' && !prices.some(p => p > 20000 && p <= 50000)) return false
        if (priceFilter === '50000+' && !prices.some(p => p > 50000)) return false
      }

      // Date filter
      if (dateFilter && event.starts_at) {
        const eventDate = new Date(event.starts_at)
        const today = new Date()
        today.setHours(0, 0, 0, 0)

        if (dateFilter === 'today') {
          const eventDay = new Date(eventDate)
          eventDay.setHours(0, 0, 0, 0)
          if (eventDay.getTime() !== today.getTime()) return false
        }
        if (dateFilter === 'tomorrow') {
          const tomorrow = new Date(today)
          tomorrow.setDate(tomorrow.getDate() + 1)
          const eventDay = new Date(eventDate)
          eventDay.setHours(0, 0, 0, 0)
          if (eventDay.getTime() !== tomorrow.getTime()) return false
        }
        if (dateFilter === 'this-week') {
          const endOfWeek = new Date(today)
          endOfWeek.setDate(endOfWeek.getDate() + (7 - endOfWeek.getDay()))
          if (eventDate < today || eventDate > endOfWeek) return false
        }
        if (dateFilter === 'this-weekend') {
          const friday = new Date(today)
          friday.setDate(friday.getDate() + (5 - friday.getDay() + 7) % 7)
          const sunday = new Date(friday)
          sunday.setDate(sunday.getDate() + 2)
          if (eventDate < friday || eventDate > sunday) return false
        }
        if (dateFilter === 'next-week') {
          const startOfNextWeek = new Date(today)
          startOfNextWeek.setDate(startOfNextWeek.getDate() + (7 - startOfNextWeek.getDay() + 7) % 7 + 1)
          const endOfNextWeek = new Date(startOfNextWeek)
          endOfNextWeek.setDate(endOfNextWeek.getDate() + 6)
          if (eventDate < startOfNextWeek || eventDate > endOfNextWeek) return false
        }
        if (dateFilter === 'this-month') {
          const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0)
          if (eventDate < today || eventDate > endOfMonth) return false
        }
      }

      return true
    })
  }

  const filteredEvents = filterEvents(events)

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <Header />
      
      <main className="flex-1">
        <section className="relative bg-white pt-8 md:pt-10 pb-16">
          <div className="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">


            {/* Top Selling Events */}
            <div className="mb-8">
              <h2 className="text-lg font-bold text-gray-900 mb-3">Top Selling Events</h2>
              <div className="flex flex-col gap-2 mb-4">
                <label className="text-sm font-semibold text-gray-700">
                  Find an event in:
                </label>
                <div className="relative" ref={dropdownRef}>
                  <button
                    type="button"
                    onClick={() => setStateDropdownOpen(!stateDropdownOpen)}
                    className="rounded-full border border-gray-300 bg-white px-4 py-2.5 pr-8 text-sm text-gray-900 font-medium shadow-sm focus:border-[#8b5cf6] focus:ring-2 focus:ring-[#8b5cf6]/20 focus:outline-none transition-all w-fit flex items-center gap-2"
                  >
                    {stateOptions.find(opt => opt.value === topSellingState)?.label || 'All states'}
                    <ChevronDown className="w-4 h-4 text-gray-500" />
                  </button>

                  {stateDropdownOpen && (
                    <div className="absolute left-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 text-gray-900 max-h-80 overflow-y-auto">
                      {stateOptions.map((option) => (
                        <button
                          key={option.value || 'all'}
                          onClick={() => {
                            setTopSellingState(option.value)
                            setStateDropdownOpen(false)
                          }}
                          className="block w-full text-left px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                        >
                          {option.label}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              {topSellingEvents.length > 0 ? (
                <div className="flex gap-5 overflow-x-auto no-scrollbar">
                  {topSellingEvents.map((event) => (
                    <Link
                      key={event.id}
                      to={`/events/${event.id}`}
                      className="group shrink-0 w-[16.8rem]"
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
                            <span className="text-4xl">🎟️</span>
                          </div>
                        )}
                        {/* Dark overlay - darker at bottom for text readability */}
                        <div className="absolute inset-0 bg-gradient-to-t from-black via-black/75 to-transparent" />
                        {/* Text inside card */}
                        <div className="absolute bottom-0 left-0 right-0 p-5">
                          <h3 className="text-[1.3rem] font-bold text-white line-clamp-2 mb-1">
                            {event.title}
                          </h3>
                          <p className="text-base text-white/90">{formatPrice(event)}</p>
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
              ) : (
                <Link
                  to="/organizer/login"
                  className="flex items-center rounded-2xl bg-gradient-to-br from-[#8b5cf6] to-black hover:shadow-xl hover:shadow-purple-500/20 transition-all duration-300 overflow-hidden group h-32"
                >
                  <div className="flex-1 p-6 flex flex-col justify-center items-center text-center">
                    <h3 className="text-lg font-bold text-white mb-1">Create Your Event Now</h3>
                    <p className="text-xs text-white/80">Start selling tickets for your unforgettable experiences</p>
                  </div>
                </Link>
              )}
            </div>

            {/* Popular events heading */}
            <div className="flex flex-col gap-4 mb-6">
              <div className="flex items-center justify-between">
                <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">
                  Popular events
                </h2>
                <Link to="/events" className="text-sm font-medium text-gray-500 hover:text-[#8b5cf6] transition-colors">
                  See all
                </Link>
              </div>
              <div className="flex flex-wrap gap-3">
                {/* Price Filter */}
                <div className="relative" ref={priceDropdownRef}>
                  <button
                    type="button"
                    onClick={() => setPriceDropdownOpen(!priceDropdownOpen)}
                    className="rounded-full border border-gray-300 bg-white px-4 py-2.5 pr-8 text-sm text-gray-900 font-medium shadow-sm focus:border-[#8b5cf6] focus:ring-2 focus:ring-[#8b5cf6]/20 focus:outline-none transition-all w-fit flex items-center gap-2"
                  >
                    {priceOptions.find(opt => opt.value === priceFilter)?.label || 'All prices'}
                    <ChevronDown className="w-4 h-4 text-gray-500" />
                  </button>

                  {priceDropdownOpen && (
                    <div className="absolute left-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 text-gray-900">
                      {priceOptions.map((option) => (
                        <button
                          key={option.value}
                          onClick={() => {
                            setPriceFilter(option.value)
                            setPriceDropdownOpen(false)
                          }}
                          className="block w-full text-left px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                        >
                          {option.label}
                        </button>
                      ))}
                    </div>
                  )}
                </div>

                {/* Date Filter */}
                <div className="relative" ref={dateDropdownRef}>
                  <button
                    type="button"
                    onClick={() => setDateDropdownOpen(!dateDropdownOpen)}
                    className="rounded-full border border-gray-300 bg-white px-4 py-2.5 pr-8 text-sm text-gray-900 font-medium shadow-sm focus:border-[#8b5cf6] focus:ring-2 focus:ring-[#8b5cf6]/20 focus:outline-none transition-all w-fit flex items-center gap-2"
                  >
                    {dateOptions.find(opt => opt.value === dateFilter)?.label || 'All dates'}
                    <ChevronDown className="w-4 h-4 text-gray-500" />
                  </button>

                  {dateDropdownOpen && (
                    <div className="absolute left-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 text-gray-900">
                      {dateOptions.map((option) => (
                        <button
                          key={option.value}
                          onClick={() => {
                            setDateFilter(option.value)
                            setDateDropdownOpen(false)
                          }}
                          className="block w-full text-left px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                        >
                          {option.label}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              </div>
            </div>

            {/* Popular events list (Compact Cards) */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {loading ? (
                <div className="py-20 text-center text-gray-400 col-span-full">
                  Loading events...
                </div>
              ) : error ? (
                <div className="py-20 text-center text-gray-500 col-span-full">
                  {error}
                </div>
              ) : filteredEvents.length === 0 ? (
                <Link
                  to="/organizer/login"
                  className="flex items-center rounded-2xl bg-gradient-to-br from-[#8b5cf6] to-black hover:shadow-xl hover:shadow-purple-500/20 transition-all duration-300 overflow-hidden group h-52 col-span-full"
                >
                  <div className="flex-1 p-8 flex flex-col justify-center items-center text-center">
                    <div className="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mb-4">
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                      </svg>
                    </div>
                    <h3 className="text-xl font-bold text-white mb-2">Create Your Event Now</h3>
                    <p className="text-sm text-white/80">Start selling tickets for your unforgettable experiences</p>
                  </div>
                </Link>
              ) : (
                filteredEvents.map((event) => (
                  <Link
                    key={event.id}
                    to={`/events/${event.id}`}
                    className="flex items-center rounded-2xl border border-purple-200 bg-white hover:shadow-xl hover:border-purple-300 transition-all duration-300 overflow-hidden group h-52"
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
                        <h3 className="text-base sm:text-lg font-bold text-gray-900 line-clamp-2 transition-colors group-hover:text-[#8b5cf6] leading-snug">
                          {event.title}
                        </h3>
                        <div className="mt-4 text-[14px] text-gray-500 font-medium">
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
                          <div className="mt-4 text-[12px] text-purple-600 font-bold">
                            by {event.organizer_name || event.organizer_username || 'Organizer'}
                          </div>
                        </div>
                        {event.description && (
                          <div className="mt-5 text-[13px] text-gray-400 line-clamp-2 leading-relaxed">
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

            {/* Recent Events */}
            <div className="mt-12">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl sm:text-3xl font-bold text-gray-900">
                  Recent Events
                </h2>
                <Link to="/events" className="text-sm font-medium text-gray-500 hover:text-[#8b5cf6] transition-colors">
                  See all
                </Link>
              </div>
              {recentEvents.length > 0 ? (
                <div className="flex gap-5 overflow-x-auto no-scrollbar">
                  {recentEvents.map((event) => (
                    <Link
                      key={event.id}
                      to={`/events/${event.id}`}
                      className="group shrink-0 w-[16.8rem]"
                    >
                      <div className="relative aspect-[3/4] rounded-xl overflow-hidden bg-gray-100">
                        {getEventImage(event) ? (
                          <img
                            src={getEventImage(event)}
                            alt={event.title}
                            className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                          />
                        ) : (
                          <div className="w-full h-full bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center">
                            <span className="text-4xl">🎟️</span>
                          </div>
                        )}
                        {/* Dark overlay */}
                        <div className="absolute inset-0 bg-gradient-to-t from-black via-black/75 to-transparent" />
                        {/* Text inside card */}
                        <div className="absolute bottom-0 left-0 right-0 p-5">
                          <h3 className="text-[1.3rem] font-bold text-white line-clamp-2 mb-1">
                            {event.title}
                          </h3>
                          <p className="text-base text-white/90">{formatPrice(event)}</p>
                        </div>
                        <div className="absolute inset-0 bg-black/40 flex items-center justify-center z-10">
                          <span className="text-white font-extrabold text-xs uppercase tracking-widest px-2 py-1 rounded bg-gray-700">Ended</span>
                        </div>
                      </div>
                    </Link>
                  ))}
                </div>
              ) : (
                <Link
                  to="/organizer/login"
                  className="flex items-center rounded-2xl bg-gradient-to-br from-[#8b5cf6] to-black hover:shadow-xl hover:shadow-purple-500/20 transition-all duration-300 overflow-hidden group h-32"
                >
                  <div className="flex-1 p-6 flex flex-col justify-center items-center text-center">
                    <h3 className="text-lg font-bold text-white mb-1">Create Your Event Now</h3>
                    <p className="text-xs text-white/80">Start selling tickets for your unforgettable experiences</p>
                  </div>
                </Link>
              )}
            </div>

          </div>
        </section>

        {/* Pricing Section */}
        <section id="pricing" className="py-16 bg-gradient-to-br from-purple-50 via-white to-blue-50">
          <div className="max-w-6xl mx-auto px-4">
            <div className="text-center mb-12">
              <h2 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
                Simple, Transparent Pricing
              </h2>
              <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                Focus on creating amazing events. We handle the rest with a simple fee structure.
              </p>
            </div>

            <div className="bg-white rounded-3xl shadow-2xl border border-purple-100 overflow-hidden max-w-3xl mx-auto">
              <div className="bg-gradient-to-r from-purple-600 to-blue-600 px-8 py-6 text-white">
                <h3 className="text-2xl font-bold">Platform Fee</h3>
                <p className="text-purple-100 mt-1">Per ticket sold</p>
              </div>
              
              <div className="p-8">
                <div className="text-center mb-8">
                  <div className="text-5xl font-bold text-gray-900 mb-2">
                    10% + ₦100
                  </div>
                  <p className="text-gray-600">of ticket price</p>
                </div>

                <div className="bg-purple-50 rounded-2xl p-6 mb-8">
                  <h4 className="font-semibold text-gray-900 mb-4">Fee Examples</h4>
                  <div className="space-y-3">
                    <div className="flex justify-between items-center py-2 border-b border-purple-200">
                      <span className="text-gray-700">₦1,000 ticket</span>
                      <span className="font-semibold text-gray-900">₦200 fee</span>
                    </div>
                    <div className="flex justify-between items-center py-2 border-b border-purple-200">
                      <span className="text-gray-700">₦5,000 ticket</span>
                      <span className="font-semibold text-gray-900">₦600 fee</span>
                    </div>
                    <div className="flex justify-between items-center py-2">
                      <span className="text-gray-700">₦10,000 ticket</span>
                      <span className="font-semibold text-gray-900">₦1,100 fee</span>
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                  <div className="bg-gray-50 rounded-xl p-4 text-center">
                    <div className="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                    </div>
                    <h5 className="font-semibold text-gray-900 text-sm">Instant Setup</h5>
                    <p className="text-xs text-gray-600 mt-1">Create events in minutes</p>
                  </div>
                  <div className="bg-gray-50 rounded-xl p-4 text-center">
                    <div className="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                    </div>
                    <h5 className="font-semibold text-gray-900 text-sm">Secure Payments</h5>
                    <p className="text-xs text-gray-600 mt-1">Protected transactions</p>
                  </div>
                  <div className="bg-gray-50 rounded-xl p-4 text-center">
                    <div className="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                    </div>
                    <h5 className="font-semibold text-gray-900 text-sm">24/7 Support</h5>
                    <p className="text-xs text-gray-600 mt-1">Always here to help</p>
                  </div>
                </div>

                <div className="text-center">
                  <Link
                    to="/register"
                    className="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all"
                  >
                    Start Selling Tickets
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                  </Link>
                  <p className="text-sm text-gray-500 mt-3">No hidden fees. No monthly costs.</p>
                </div>
              </div>
            </div>

            {/* FAQ */}
            <div className="max-w-3xl mx-auto mt-12">
              <h3 className="text-xl font-bold text-gray-900 mb-6 text-center">Frequently Asked Questions</h3>
              <div className="space-y-4">
                <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                  <h4 className="font-semibold text-gray-900 mb-2">Is there a monthly fee?</h4>
                  <p className="text-gray-600 text-sm">No! You only pay when you sell tickets. No monthly subscription or hidden costs.</p>
                </div>
                <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                  <h4 className="font-semibold text-gray-900 mb-2">When do I get paid?</h4>
                  <p className="text-gray-600 text-sm">Payouts are processed automatically after your event ends. You can withdraw your earnings anytime.</p>
                </div>
                <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                  <h4 className="font-semibold text-gray-900 mb-2">Can I offer free tickets?</h4>
                  <p className="text-gray-600 text-sm">Yes! Free events have no platform fee. You only pay when selling paid tickets.</p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </div>
  )
}

export default Home
