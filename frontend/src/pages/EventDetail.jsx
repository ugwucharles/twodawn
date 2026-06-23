import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { getEventById } from '../services/events'
import Header from '../components/Header'
import Footer from '../components/Footer'

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

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12">Loading event...</div>
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
        <div className="max-w-6xl md:max-w-7xl mx-auto px-4 md:px-6 lg:px-10">
          <Link to="/events" className="text-indigo-600 hover:text-indigo-800 mb-6 inline-block">
            ← Back to Events
          </Link>
          
          <div className="bg-white rounded-lg shadow-md overflow-hidden mt-6">
            <div className="p-8">
              <h1 className="text-3xl font-bold text-gray-900 mb-4">{event.title}</h1>
              <div className="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <span>Hosted by</span>
                <span className="font-bold text-gray-900">{event.organizer_name || event.organizer_username || 'Organizer'}</span>
                {event.organizer_username && <span className="text-gray-400">(@{event.organizer_username})</span>}
              </div>
              <div className="space-y-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">Venue</h3>
                  <p className="text-gray-600">{event.venue}</p>
                </div>
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">Date & Time</h3>
                  <p className="text-gray-600">
                    {event.starts_at ? new Date(event.starts_at).toLocaleString() : 'TBD'}
                  </p>
                </div>
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">Price</h3>
                  <p className="text-2xl font-bold text-[#8b5cf6]">
                    ₦{event.price || 'Free'}
                  </p>
                </div>
                {event.description && (
                  <div>
                    <h3 className="text-lg font-semibold text-gray-900">Description</h3>
                    <p className="text-gray-600">{event.description}</p>
                  </div>
                )}
              </div>
              
              <div className="mt-8">
                <Link
                  to={`/events/${event.id}/checkout`}
                  className="inline-block bg-[#8b5cf6] text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-[#7c3aed]"
                >
                  Buy Ticket
                </Link>
              </div>
            </div>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  )
}

export default EventDetail
