import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { Plus, Calendar, MapPin, Users, DollarSign } from 'lucide-react';

function Events() {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      const response = await axios.get('/organizer/events');
      setEvents(response.data.events || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load events', err);
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="text-center py-12">Loading events...</div>;
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
      {/* Header */}
      <div className="mb-8 flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-black text-gray-900 tracking-tight">Events</h1>
          <p className="text-gray-500 mt-1 font-medium">Manage your events</p>
        </div>
        <Link to="/organizer/events/create" className="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all">
          <Plus className="w-5 h-5 mr-2" />
          Create Event
        </Link>
      </div>

      {/* Events List */}
      <div className="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 overflow-hidden">
        {events.length > 0 ? (
          events.map((event) => (
            <div key={event.id} className="p-6 border-b border-gray-100/50 hover:bg-gray-50/50 transition-colors">
              <div className="flex items-center gap-6 overflow-x-auto custom-scrollbar pb-2">
                {/* Event Image */}
                {event.image_url ? (
                  <img src={event.image_url} alt={event.title} className="w-20 h-20 rounded-2xl object-cover shrink-0" />
                ) : (
                  <div className="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-100 to-indigo-200 shrink-0"></div>
                )}
                
                {/* Event Details */}
                <div className="flex-1 min-w-[200px] shrink-0">
                  <h3 className="text-lg font-bold text-gray-900 truncate">{event.title}</h3>
                  <p className="text-sm text-gray-500 mt-1">
                    {event.starts_at ? new Date(event.starts_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) : ''}
                  </p>
                  <p className="text-sm text-gray-500 mt-1">{event.venue || 'No venue'}</p>
                </div>
                
                {/* Stats */}
                <div className="flex items-center gap-6 shrink-0">
                  <div className="text-center">
                    <p className="text-2xl font-black text-gray-900">{event.orders_count || 0}</p>
                    <p className="text-xs text-gray-500 font-medium">Tickets Sold</p>
                  </div>
                  <div className="text-center">
                    <p className="text-2xl font-black text-gray-900">₦{((event.revenue || 0) / 100).toFixed(0)}</p>
                    <p className="text-xs text-gray-500 font-medium">Revenue</p>
                  </div>
                </div>
                
                {/* Actions */}
                <div className="flex items-center gap-2 shrink-0">
                  <Link to={`/organizer/events/${event.id}`} className="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors whitespace-nowrap">
                    View
                  </Link>
                  <Link to={`/organizer/events/${event.id}/edit`} className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors whitespace-nowrap">
                    Edit
                  </Link>
                </div>
              </div>
            </div>
          ))
        ) : (
          <div className="p-20 text-center">
            <Calendar className="h-16 w-16 mx-auto mb-4 text-gray-300" />
            <p className="text-xl font-bold text-gray-900 mb-2">No events yet</p>
            <p className="text-gray-500 mb-6">Create your first event to get started</p>
            <Link to="/organizer/events/create" className="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all">
              <Plus className="w-5 h-5 mr-2" />
              Create Event
            </Link>
          </div>
        )}
      </div>

      <style>{`
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #CBD5E0; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  );
}

export default Events;
