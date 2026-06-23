import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Plus, Calendar, Eye, EyeOff } from 'lucide-react'
import api from '../../services/api'
import { getEventImage } from '../../utils/image'

function Events() {
  const [events, setEvents] = useState([])
  const [loading, setLoading] = useState(true)
  const [toggling, setToggling] = useState(null)

  useEffect(() => {
    fetchEvents()
  }, [])

  const fetchEvents = async () => {
    try {
      const response = await api.get('/organizer/events')
      setEvents(response.data.events || [])
    } catch (err) {
      console.error('Failed to load events', err)
    } finally {
      setLoading(false)
    }
  }

  const togglePublish = async (eventId, currentStatus) => {
    setToggling(eventId)
    try {
      const res = await api.patch(`/organizer/events/${eventId}/toggle-publish`)
      setEvents(prev =>
        prev.map(e => e.id === eventId ? { ...e, is_published: res.data.is_published } : e)
      )
    } catch (err) {
      console.error('Failed to toggle publish', err)
    } finally {
      setToggling(null)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center py-24">
        <div className="w-7 h-7 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin" />
      </div>
    )
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Header */}
      <div className="mb-8 flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-black text-gray-900 tracking-tight">Events</h1>
          <p className="text-gray-500 mt-1 font-medium">Manage your events</p>
        </div>
        <Link
          to="/organizer/events/create"
          className="inline-flex items-center px-5 py-2.5 bg-[#8b5cf6] hover:bg-[#7c3aed] text-white font-bold rounded-xl shadow-lg shadow-purple-200 transition-all text-sm"
        >
          <Plus className="w-4 h-4 mr-2" />
          Create Event
        </Link>
      </div>

      {/* Events List */}
      <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {events.length > 0 ? (
          <div className="divide-y divide-gray-50">
            {events.map((event) => {
              const img = getEventImage(event)
              return (
                <div key={event.id} className="overflow-x-auto">
                  <div className="flex items-center gap-4 p-4 min-w-[600px] hover:bg-gray-50/60 transition-colors">
                    {/* Thumbnail */}
                    {img ? (
                      <img
                        src={img}
                        alt={event.title}
                        className="w-16 h-16 rounded-2xl object-cover shrink-0"
                      />
                    ) : (
                      <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-100 to-purple-200 shrink-0 flex items-center justify-center">
                        <span className="text-xl font-black text-purple-300">{event.title?.charAt(0)}</span>
                      </div>
                    )}

                    {/* Event Details */}
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2 mb-0.5">
                        <h3 className="text-base font-bold text-gray-900 truncate">{event.title}</h3>
                        <span className={`shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${
                          event.is_published
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-100 text-gray-500'
                        }`}>
                          {event.is_published ? '● Live' : '○ Draft'}
                        </span>
                      </div>
                      <p className="text-xs text-gray-400 truncate">
                        {event.starts_at
                          ? new Date(event.starts_at).toLocaleString('en-US', {
                              month: 'short', day: 'numeric', year: 'numeric',
                              hour: 'numeric', minute: '2-digit'
                            })
                          : ''}
                        {event.venue ? ` · ${event.venue}` : ''}
                      </p>
                    </div>

                    {/* Stats */}
                    <div className="flex items-center gap-6 shrink-0">
                      <div className="text-center">
                        <p className="text-xl font-black text-gray-900">{event.orders_count || 0}</p>
                        <p className="text-xs text-gray-400 font-medium">Sold</p>
                      </div>
                      <div className="text-center">
                        <p className="text-xl font-black text-gray-900">₦{(event.revenue || 0).toLocaleString()}</p>
                        <p className="text-xs text-gray-400 font-medium">Revenue</p>
                      </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center gap-2 shrink-0">
                      <button
                        onClick={() => togglePublish(event.id, event.is_published)}
                        disabled={toggling === event.id}
                        title={event.is_published ? 'Unpublish event' : 'Publish event'}
                        className={`flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition-all disabled:opacity-50 ${
                          event.is_published
                            ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200'
                            : 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200'
                        }`}
                      >
                        {toggling === event.id ? (
                          <span className="w-3.5 h-3.5 border-2 border-current/30 border-t-current rounded-full animate-spin" />
                        ) : event.is_published ? (
                          <EyeOff className="w-3.5 h-3.5" />
                        ) : (
                          <Eye className="w-3.5 h-3.5" />
                        )}
                        {event.is_published ? 'Unpublish' : 'Publish'}
                      </button>

                      <Link
                        to={`/organizer/events/${event.id}`}
                        className="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors text-xs whitespace-nowrap"
                      >
                        View
                      </Link>
                      <Link
                        to={`/organizer/events/${event.id}/edit`}
                        className="px-3 py-2 bg-[#8b5cf6] hover:bg-[#7c3aed] text-white font-bold rounded-xl transition-colors text-xs whitespace-nowrap"
                      >
                        Edit
                      </Link>
                    </div>
                  </div>
                </div>
              )
            })}
          </div>
        ) : (
          <div className="p-20 text-center">
            <Calendar className="h-14 w-14 mx-auto mb-4 text-gray-200" />
            <p className="text-lg font-bold text-gray-900 mb-2">No events yet</p>
            <p className="text-gray-400 mb-6 text-sm">Create your first event to get started</p>
            <Link
              to="/organizer/events/create"
              className="inline-flex items-center px-6 py-3 bg-[#8b5cf6] hover:bg-[#7c3aed] text-white font-bold rounded-2xl shadow-lg shadow-purple-200 transition-all"
            >
              <Plus className="w-5 h-5 mr-2" />
              Create Event
            </Link>
          </div>
        )}
      </div>
    </div>
  )
}

export default Events
