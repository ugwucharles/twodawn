import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeft, Plus, Trash2, Link } from 'lucide-react';
import api from '../../services/api';

function CreateEvent() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    title: '',
    custom_slug: '',
    description: '',
    must_know: '',
    venue: '',
    state: '',
    starts_at: '',
    ends_at: '',
    price: '',
    capacity: '',
    pass_fees_to_buyer: false,
    image: null,
    ticket_types: []
  });
  const [errors, setErrors] = useState([]);
  const [submitting, setSubmitting] = useState(false);

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
  };

  const addTicketType = () => {
    setFormData({
      ...formData,
      ticket_types: [...formData.ticket_types, { name: '', price: '' }]
    });
  };

  const removeTicketType = (index) => {
    setFormData({
      ...formData,
      ticket_types: formData.ticket_types.filter((_, i) => i !== index)
    });
  };

  const updateTicketType = (index, field, value) => {
    const updated = [...formData.ticket_types];
    updated[index][field] = value;
    setFormData({ ...formData, ticket_types: updated });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrors([]);

    try {
      const data = new FormData();
      Object.keys(formData).forEach(key => {
        if (key === 'ticket_types') {
          data.append(key, JSON.stringify(formData[key]));
        } else if (key === 'image' && formData[key]) {
          data.append(key, formData[key]);
        } else if (key !== 'image') {
          data.append(key, formData[key]);
        }
      });

      await api.post('/organizer/events', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      navigate('/organizer/events');
    } catch (err) {
      console.error('Failed to create event', err);
      if (err.response?.data?.errors) {
        setErrors(Object.values(err.response.data.errors).flat());
      } else if (err.response?.data?.message) {
        setErrors([err.response.data.message]);
      } else {
        setErrors(['Failed to create event']);
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="max-w-4xl mx-auto py-8 px-6 animate-fade-in">
      {/* Header */}
      <div className="mb-8">
        <a
          href="/organizer/dashboard"
          onClick={(e) => { e.preventDefault(); navigate('/organizer/dashboard'); }}
          className="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-900 mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Dashboard
        </a>
        <h1 className="text-3xl font-black text-gray-900 tracking-tight">Create Event</h1>
        <p className="text-gray-500 mt-1 font-medium">Fill in the details to create your new event</p>
      </div>

      {errors.length > 0 && (
        <div className="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
          <div className="flex items-center gap-3">
            <div className="w-5 h-5 text-red-600">!</div>
            <div className="text-sm font-bold text-red-800">
              {errors[0]}
            </div>
          </div>
        </div>
      )}

      <div className="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 p-8">
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="md:col-span-2">
              <label className="block text-sm font-bold text-gray-700 mb-2">Event Title *</label>
              <input
                type="text"
                value={formData.title}
                onChange={(e) => {
                  const title = e.target.value;
                  const autoSlug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                  setFormData({ ...formData, title, custom_slug: formData.custom_slug || autoSlug });
                }}
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-purple-400 focus:ring-2 focus:ring-purple-100 transition-all"
                placeholder="Enter event title"
                required
                autoFocus
              />
            </div>

            {/* Custom URL slug */}
            <div className="md:col-span-2">
              <label className="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                <Link className="w-4 h-4 text-purple-500" />
                Custom Event URL
              </label>
              <div className="flex rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                <span className="inline-flex items-center px-3 text-gray-400 text-sm bg-gray-50 border-r border-gray-200 whitespace-nowrap">
                  twodawn.com.ng/e/
                </span>
                <input
                  type="text"
                  value={formData.custom_slug}
                  onChange={(e) => {
                    const val = e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
                    setFormData({ ...formData, custom_slug: val });
                  }}
                  className="w-full px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                  placeholder="my-event-name"
                />
              </div>
              <p className="text-xs text-gray-400 mt-1.5">Lowercase letters, numbers, and dashes only. Auto-filled from title — edit to customize.</p>
            </div>

            <div className="md:col-span-2">
              <label className="block text-sm font-bold text-gray-700 mb-2">Description</label>
              <textarea
                value={formData.description}
                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                rows="4"
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                placeholder="Describe your event"
              />
            </div>

            <div className="md:col-span-2">
              <label className="block text-sm font-bold text-gray-700 mb-2">MUST KNOW! (Important Info)</label>
              <textarea
                value={formData.must_know}
                onChange={(e) => setFormData({ ...formData, must_know: e.target.value })}
                rows="3"
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                placeholder="Important info for attendees (dress code, gate times, etc.)"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-700 mb-2">Venue *</label>
              <input
                type="text"
                value={formData.venue}
                onChange={(e) => setFormData({ ...formData, venue: e.target.value })}
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                placeholder="Event venue"
                required
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-700 mb-2">State *</label>
              <select
                value={formData.state}
                onChange={(e) => setFormData({ ...formData, state: e.target.value })}
                required
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
              >
                <option value="" disabled>Select state</option>
                {Object.entries(ngStates).map(([code, label]) => (
                  <option key={code} value={code}>{label}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-700 mb-2">Start Date & Time *</label>
              <input
                type="datetime-local"
                value={formData.starts_at}
                onChange={(e) => setFormData({ ...formData, starts_at: e.target.value })}
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                required
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-700 mb-2">End Date & Time</label>
              <input
                type="datetime-local"
                value={formData.ends_at}
                onChange={(e) => setFormData({ ...formData, ends_at: e.target.value })}
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-700 mb-2">Price (₦)</label>
              <input
                type="number"
                value={formData.price || ''}
                onChange={(e) => setFormData({ ...formData, price: e.target.value ? parseFloat(e.target.value) : '' })}
                step="0.01"
                min="0"
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                placeholder="Leave blank for free event"
              />
            </div>

            <div>
              <label className="block text-sm font-bold text-gray-700 mb-2">Capacity</label>
              <input
                type="number"
                value={formData.capacity}
                onChange={(e) => setFormData({ ...formData, capacity: e.target.value })}
                step="1"
                min="1"
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                placeholder="Maximum attendees"
              />
            </div>

            <div className="md:col-span-2">
              <label className="inline-flex items-start gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  checked={formData.pass_fees_to_buyer}
                  onChange={(e) => setFormData({ ...formData, pass_fees_to_buyer: e.target.checked })}
                  className="mt-1 w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                />
                <div>
                  <span className="block text-sm font-bold text-gray-700">Pass ticket fee to buyers</span>
                  <span className="block text-xs text-gray-500 mt-1">If enabled, the 10% + ₦100 platform service fee will be added to the ticket price paid by the customer. Otherwise, the fee will be deducted from your payout.</span>
                </div>
              </label>
            </div>

            <div className="md:col-span-2">
              <label className="block text-sm font-bold text-gray-700 mb-2">Ticket Types (Optional)</label>
              <p className="text-xs text-gray-500 mb-3">Add multiple ticket tiers (e.g. Regular, VIP). If added, these override the base price above.</p>

              <div className="space-y-3">
                {formData.ticket_types.map((ticket, index) => (
                  <div key={index} className="flex items-center gap-3">
                    <div className="flex-1">
                      <input
                        type="text"
                        value={ticket.name}
                        onChange={(e) => updateTicketType(index, 'name', e.target.value)}
                        placeholder="Ticket Name (e.g. VIP)"
                        className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                        required
                      />
                    </div>
                    <div className="flex-1">
                      <input
                        type="number"
                        value={ticket.price || ''}
                        onChange={(e) => updateTicketType(index, 'price', e.target.value ? parseFloat(e.target.value) : '')}
                        step="0.01"
                        min="0"
                        placeholder="Price (₦)"
                        className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                        required
                      />
                    </div>
                    <button
                      type="button"
                      onClick={() => removeTicketType(index)}
                      className="p-2 text-red-500 hover:text-red-700"
                    >
                      <Trash2 className="h-5 w-5" />
                    </button>
                  </div>
                ))}
              </div>

              <button
                type="button"
                onClick={addTicketType}
                className="mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-200 transition-colors flex items-center gap-2"
              >
                <Plus className="w-4 h-4" />
                Add Ticket Type
              </button>
            </div>

            <div className="md:col-span-2">
              <label className="block text-sm font-bold text-gray-700 mb-2">Event Flyer</label>
              <input
                type="file"
                accept="image/*"
                onChange={(e) => setFormData({ ...formData, image: e.target.files[0] })}
                className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
              />
              <p className="text-xs text-gray-500 mt-1">Upload an attractive flyer for your event</p>
            </div>
          </div>

          <div className="flex items-center gap-4 pt-6 border-t border-gray-100">
            <button
              type="submit"
              disabled={submitting}
              className="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transition-all disabled:opacity-50"
            >
              {submitting ? 'Creating...' : 'Create Event'}
            </button>
            <button
              type="button"
              onClick={() => navigate('/organizer/dashboard')}
              className="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-2xl transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>

      <style>{`
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  );
}

export default CreateEvent;
