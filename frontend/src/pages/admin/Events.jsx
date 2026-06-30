import { useState, useEffect } from 'react';
import api from '../../services/api';
import { Calendar, Plus, Edit2, Trash2, Globe, EyeOff, Search } from 'lucide-react';

function AdminEvents() {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  
  // Modal states
  const [modalOpen, setModalOpen] = useState(false);
  const [editingEvent, setEditingEvent] = useState(null);
  
  // Form states
  const [formData, setFormData] = useState({
    title: '',
    venue: '',
    starts_at: '',
    price: 0,
    capacity: 100,
    is_published: 0,
  });

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      const response = await api.get('/ucc/events/list');
      setEvents(response.data.events || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load events', err);
      setLoading(false);
    }
  };

  const handleTogglePublish = async (event) => {
    try {
      const res = await api.patch(`/ucc/events/${event.id}/toggle-json`);
      if (res.data.ok) {
        setEvents(events.map(e => e.id === event.id ? { ...e, is_published: e.is_published ? 0 : 1 } : e));
      }
    } catch (err) {
      console.error('Failed to toggle event status', err);
    }
  };

  const handleDeleteEvent = async (eventId) => {
    if (!window.confirm('Are you sure you want to delete this event?')) return;
    try {
      const res = await api.delete(`/ucc/events/${eventId}`);
      if (res.data.ok) {
        setEvents(events.filter(e => e.id !== eventId));
      }
    } catch (err) {
      console.error('Failed to delete event', err);
    }
  };

  const openCreateModal = () => {
    setEditingEvent(null);
    setFormData({
      title: '',
      venue: '',
      starts_at: '',
      price: 0,
      capacity: 100,
      is_published: 0,
    });
    setModalOpen(true);
  };

  const openEditModal = (event) => {
    setEditingEvent(event);
    setFormData({
      title: event.title || '',
      venue: event.venue || '',
      starts_at: event.starts_at ? event.starts_at.slice(0, 16) : '',
      price: event.price || 0,
      capacity: event.capacity || 100,
      is_published: event.is_published || 0,
    });
    setModalOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingEvent) {
        // Edit Mode
        const res = await api.patch(`/ucc/events/${editingEvent.id}`, formData);
        if (res.data.ok) {
          fetchEvents();
          setModalOpen(false);
        }
      } else {
        // Create Mode
        const res = await api.post('/ucc/events', formData);
        if (res.data.ok) {
          fetchEvents();
          setModalOpen(false);
        }
      }
    } catch (err) {
      console.error('Failed to save event', err);
      alert(err.response?.data?.error || 'Failed to save event');
    }
  };

  const filteredEvents = events.filter(e => 
    e.title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    e.venue?.toLowerCase().includes(searchTerm.toLowerCase())
  );

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-white">Events Management</h1>
          <p className="text-gray-400 mt-1">Manage and publish all platform events</p>
        </div>
        <button
          onClick={openCreateModal}
          className="flex items-center space-x-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors"
        >
          <Plus className="w-5 h-5" />
          <span>Add Event</span>
        </button>
      </div>

      {/* Search Filter */}
      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
        <input
          type="text"
          placeholder="Search events by title or venue..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="w-full pl-10 pr-4 py-2.5 bg-gray-900 border border-gray-800 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
        />
      </div>

      {/* Events List */}
      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="space-y-4">
          {filteredEvents.length > 0 ? (
            filteredEvents.map((event, index) => (
              <div key={index} className="flex flex-col md:flex-row md:items-center justify-between p-4 bg-gray-800/30 border border-gray-800/50 rounded-lg gap-4 hover:border-gray-700 transition-colors">
                <div>
                  <p className="text-sm font-semibold text-white">{event.title}</p>
                  <p className="text-xs text-gray-500 mt-1">
                    {new Date(event.starts_at).toLocaleDateString()} • {event.venue || 'TBD'}
                  </p>
                  <div className="flex space-x-4 mt-2">
                    <span className="text-xs text-gray-400">Capacity: {event.capacity || 100}</span>
                    <span className="text-xs text-gray-400">Price: ₦{((event.price || 0) / 100).toLocaleString()}</span>
                  </div>
                </div>
                <div className="flex items-center space-x-4">
                  {/* Toggle publish button */}
                  <button
                    onClick={() => handleTogglePublish(event)}
                    title={event.is_published ? 'Unpublish Event' : 'Publish Event'}
                    className={`flex items-center space-x-2 px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors ${
                      event.is_published
                        ? 'bg-green-500/10 text-green-400 border-green-500/20 hover:bg-green-500/20'
                        : 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20 hover:bg-yellow-500/20'
                    }`}
                  >
                    {event.is_published ? <Globe className="w-3.5 h-3.5" /> : <EyeOff className="w-3.5 h-3.5" />}
                    <span>{event.is_published ? 'Published' : 'Draft'}</span>
                  </button>
                  
                  {/* Edit button */}
                  <button
                    onClick={() => openEditModal(event)}
                    className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors border border-gray-800"
                  >
                    <Edit2 className="w-4 h-4" />
                  </button>

                  {/* Delete button */}
                  <button
                    onClick={() => handleDeleteEvent(event.id)}
                    className="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors border border-gray-800"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-12">
              <Calendar className="w-12 h-12 mx-auto text-gray-500 mb-4" />
              <p className="text-gray-500">No events found matching your search</p>
            </div>
          )}
        </div>
      </div>

      {/* Create / Edit Event Modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="w-full max-w-lg bg-gray-900 border border-gray-800 rounded-xl shadow-2xl p-6 space-y-6">
            <div className="flex items-center justify-between border-b border-gray-800 pb-4">
              <h2 className="text-xl font-bold text-white">{editingEvent ? 'Edit Event Details' : 'Create New Event'}</h2>
              <button onClick={() => setModalOpen(false)} className="text-gray-500 hover:text-white">&times;</button>
            </div>
            
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-gray-400 uppercase">Event Title</label>
                <input
                  type="text"
                  required
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="e.g. Neon Horizon Concert"
                  className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-400 uppercase">Venue</label>
                <input
                  type="text"
                  required
                  value={formData.venue}
                  onChange={(e) => setFormData({ ...formData, venue: e.target.value })}
                  placeholder="e.g. Grand Arena, Lagos"
                  className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-400 uppercase">Ticket Price (₦)</label>
                  <input
                    type="number"
                    required
                    value={formData.price}
                    onChange={(e) => setFormData({ ...formData, price: Number(e.target.value) })}
                    className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-400 uppercase">Total Capacity</label>
                  <input
                    type="number"
                    required
                    value={formData.capacity}
                    onChange={(e) => setFormData({ ...formData, capacity: Number(e.target.value) })}
                    className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-400 uppercase">Event Date & Time</label>
                <input
                  type="datetime-local"
                  required
                  value={formData.starts_at}
                  onChange={(e) => setFormData({ ...formData, starts_at: e.target.value })}
                  className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div className="flex items-center space-x-2 pt-2">
                <input
                  type="checkbox"
                  id="is_published"
                  checked={formData.is_published === 1}
                  onChange={(e) => setFormData({ ...formData, is_published: e.target.checked ? 1 : 0 })}
                  className="rounded border-gray-700 bg-gray-800 text-purple-600 focus:ring-purple-500"
                />
                <label htmlFor="is_published" className="text-sm text-gray-300">Publish immediately</label>
              </div>

              <div className="flex items-center justify-end space-x-3 pt-4 border-t border-gray-800">
                <button
                  type="button"
                  onClick={() => setModalOpen(false)}
                  className="px-4 py-2 border border-gray-800 hover:bg-gray-800 text-gray-400 rounded-lg text-sm transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition-colors"
                >
                  Save Event
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default AdminEvents;
