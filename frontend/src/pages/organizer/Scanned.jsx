import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import { Users, Clock, CheckCircle, ArrowLeft, Calendar, MapPin } from 'lucide-react';

function Scanned() {
  const [events, setEvents] = useState([]);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      setLoading(true);
      const res = await api.get('/organizer/events');
      setEvents(res.data?.events || []);
    } catch (err) {
      setError('Failed to load events');
      console.error('Error fetching events:', err);
      setEvents([]);
    } finally {
      setLoading(false);
    }
  };

  const fetchEventDetails = async (eventId) => {
    try {
      const res = await api.get(`/organizer/events/${eventId}`);
      setSelectedEvent(res.data);
    } catch (err) {
      console.error('Error fetching event details:', err);
    }
  };

  const handleEventClick = (event) => {
    fetchEventDetails(event.id);
  };

  const handleBack = () => {
    setSelectedEvent(null);
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-gray-500">Loading...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-red-500">{error}</div>
      </div>
    );
  }

  if (selectedEvent) {
    return (
      <div className="animate-fade-in">
        <button
          onClick={handleBack}
          className="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6 font-medium"
        >
          <ArrowLeft className="w-4 h-4" />
          Back to Events
        </button>

        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
          <h1 className="text-2xl font-bold text-gray-900 mb-2">{selectedEvent.title}</h1>
          <div className="flex items-center gap-4 text-sm text-gray-600">
            <div className="flex items-center gap-2">
              <Calendar className="w-4 h-4" />
              {new Date(selectedEvent.starts_at).toLocaleDateString()}
            </div>
            <div className="flex items-center gap-2">
              <MapPin className="w-4 h-4" />
              {selectedEvent.location}
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div className="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-2xl p-6">
            <div className="flex items-center gap-3 mb-2">
              <Users className="w-5 h-5 text-purple-600" />
              <span className="text-sm font-semibold text-gray-700">Total Tickets</span>
            </div>
            <p className="text-3xl font-bold text-gray-900">{selectedEvent.orders_count || 0}</p>
          </div>
          <div className="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-2xl p-6">
            <div className="flex items-center gap-3 mb-2">
              <CheckCircle className="w-5 h-5 text-emerald-600" />
              <span className="text-sm font-semibold text-gray-700">Scanned</span>
            </div>
            <p className="text-3xl font-bold text-gray-900">{selectedEvent.scanned_count || selectedEvent.orders_count || 0}</p>
          </div>
          <div className="bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 rounded-2xl p-6">
            <div className="flex items-center gap-3 mb-2">
              <Clock className="w-5 h-5 text-amber-600" />
              <span className="text-sm font-semibold text-gray-700">Remaining</span>
            </div>
            <p className="text-3xl font-bold text-gray-900">
              {(selectedEvent.orders_count || 0) - (selectedEvent.scanned_count || 0)}
            </p>
          </div>
        </div>

        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 className="text-lg font-bold text-gray-900 mb-4">Scanned Attendees</h2>
          {selectedEvent.scanned_attendees && selectedEvent.scanned_attendees.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-gray-200">
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Name</th>
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Email</th>
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Quantity</th>
                    <th className="text-left py-3 px-4 text-sm font-semibold text-gray-700">Scanned At</th>
                  </tr>
                </thead>
                <tbody>
                  {selectedEvent.scanned_attendees.map((attendee, idx) => (
                    <tr key={idx} className="border-b border-gray-100">
                      <td className="py-3 px-4 text-sm text-gray-900">{attendee.buyer_name}</td>
                      <td className="py-3 px-4 text-sm text-gray-600">{attendee.buyer_email}</td>
                      <td className="py-3 px-4 text-sm text-gray-900">{attendee.quantity}</td>
                      <td className="py-3 px-4 text-sm text-gray-600">
                        {attendee.scanned_at ? new Date(attendee.scanned_at).toLocaleString() : 'N/A'}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-gray-500 text-sm">No scanned attendees yet</p>
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="animate-fade-in">
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Scanned Tickets</h1>
      
      {events.length === 0 ? (
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
          <Users className="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <p className="text-gray-500">No events found</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {events.map((event) => (
            <div
              key={event.id}
              onClick={() => handleEventClick(event)}
              className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 cursor-pointer hover:shadow-md hover:border-purple-200 transition-all"
            >
              <h3 className="text-lg font-bold text-gray-900 mb-2">{event.title}</h3>
              <div className="flex items-center gap-2 text-sm text-gray-600 mb-4">
                <Calendar className="w-4 h-4" />
                {new Date(event.starts_at).toLocaleDateString()}
              </div>
              <div className="flex items-center justify-between text-sm">
                <div className="flex items-center gap-2">
                  <CheckCircle className="w-4 h-4 text-emerald-500" />
                  <span className="text-gray-700">{event.scanned_count || event.orders_count || 0} scanned</span>
                </div>
                <div className="flex items-center gap-2">
                  <Users className="w-4 h-4 text-purple-500" />
                  <span className="text-gray-700">{event.orders_count || 0} total</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default Scanned;
