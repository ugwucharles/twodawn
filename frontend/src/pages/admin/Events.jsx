import { useState, useEffect } from 'react';
import axios from 'axios';
import { Calendar } from 'lucide-react';

function AdminEvents() {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_BACKEND_URL}/admin/events/list`);
      setEvents(response.data.events || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load events', err);
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-white">Events Management</h1>
        <p className="text-gray-400 mt-1">Manage all events on the platform</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="space-y-4">
          {events.length > 0 ? (
            events.map((event, index) => (
              <div key={index} className="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                <div>
                  <p className="text-sm font-medium text-white">{event.title}</p>
                  <p className="text-xs text-gray-500 mt-1">
                    {new Date(event.starts_at).toLocaleDateString()} • {event.venue}
                  </p>
                </div>
                <div className="flex items-center space-x-2">
                  <span className={`px-2 py-1 text-xs rounded ${event.is_published ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'}`}>
                    {event.is_published ? 'Published' : 'Draft'}
                  </span>
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-12">
              <Calendar className="w-12 h-12 mx-auto text-gray-500 mb-4" />
              <p className="text-gray-500">No events found</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default AdminEvents;
