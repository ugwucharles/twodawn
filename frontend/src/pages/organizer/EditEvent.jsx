import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { ArrowLeft } from 'lucide-react';

function EditEvent() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    must_know: '',
    venue: '',
    state: '',
    starts_at: '',
    ends_at: '',
    price: '',
    capacity: '',
    pass_fees_to_buyer: false,
    custom_slug: '',
    use_custom_slug: false,
    image: null,
    gallery: []
  });
  const [errors, setErrors] = useState([]);
  const [submitting, setSubmitting] = useState(false);
  const [loading, setLoading] = useState(true);
  const [currentImage, setCurrentImage] = useState(null);

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

  useEffect(() => {
    fetchEventDetails();
  }, [id]);

  const fetchEventDetails = async () => {
    try {
      const response = await axios.get(`/organizer/events/${id}`);
      const event = response.data.event;
      setFormData({
        title: event.title || '',
        description: event.description || '',
        must_know: event.must_know || '',
        venue: event.venue || '',
        state: event.state || '',
        starts_at: event.starts_at || '',
        ends_at: event.ends_at || '',
        price: event.price || '',
        capacity: event.capacity || '',
        pass_fees_to_buyer: event.pass_fees_to_buyer || false,
        custom_slug: event.custom_slug || '',
        use_custom_slug: event.use_custom_slug || false,
        image: null
      });
      setCurrentImage(event.image_url || null);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load event details', err);
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrors([]);

    try {
      // Send only text fields as JSON
      const data = {
        title: formData.title,
        description: formData.description,
        must_know: formData.must_know,
        venue: formData.venue,
        state: formData.state,
        starts_at: formData.starts_at,
        ends_at: formData.ends_at,
        price: formData.price,
        capacity: formData.capacity,
        pass_fees_to_buyer: formData.pass_fees_to_buyer,
        custom_slug: formData.custom_slug,
        use_custom_slug: formData.use_custom_slug
      };

      await axios.patch(`/organizer/events/${id}`, data);
      navigate(`/organizer/events/${id}`);
    } catch (err) {
      console.error('Failed to update event', err);
      if (err.response?.data?.errors) {
        setErrors(Object.values(err.response.data.errors).flat());
      } else if (err.response?.data?.message) {
        setErrors([err.response.data.message]);
      } else {
        setErrors(['Failed to update event']);
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return <div className="text-center py-12">Loading event details...</div>;
  }

  return (
    <div className="max-w-4xl mx-auto py-8 px-6 animate-fade-in">
      {/* Header */}
      <div className="mb-10 flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-black text-gray-900">Edit Event</h1>
          <p className="text-gray-500 text-sm mt-1">Update the details for "{formData.title}"</p>
        </div>
        <button
          onClick={() => navigate(`/organizer/events/${id}`)}
          className="text-sm font-bold text-gray-500 hover:text-gray-700"
        >
          Back to Details
        </button>
      </div>

      {errors.length > 0 && (
        <div className="mb-8 p-5 bg-red-50 text-red-700 rounded-2xl text-sm border border-red-200">
          <ul className="list-disc list-inside space-y-2">
            {errors.map((error, index) => (
              <li key={index} className="font-medium">{error}</li>
            ))}
          </ul>
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-8">
        <div className="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-purple-200 space-y-6">
          <h2 className="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Details</h2>

          <div>
            <label htmlFor="title" className="block text-sm font-semibold text-gray-700 mb-2">Event Title *</label>
            <input
              id="title"
              type="text"
              value={formData.title}
              onChange={(e) => setFormData({ ...formData, title: e.target.value })}
              required
              className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
              placeholder="e.g. Lagos Tech Fest 2026"
            />
          </div>

          <div>
            <label htmlFor="description" className="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea
              id="description"
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              rows="5"
              className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all resize-none"
              placeholder="Tell people what this event is about..."
            />
          </div>

          <div>
            <label htmlFor="must_know" className="block text-sm font-semibold text-gray-700 mb-2">
              MUST KNOW!
              <span className="text-xs font-normal text-gray-400 ml-1">Important info for attendees</span>
            </label>
            <textarea
              id="must_know"
              value={formData.must_know}
              onChange={(e) => setFormData({ ...formData, must_know: e.target.value })}
              rows="4"
              className="block w-full rounded-xl border border-purple-200 bg-purple-50/30 text-gray-900 focus:border-purple-400 focus:ring-2 focus:ring-purple-100 px-5 py-3.5 text-sm shadow-sm transition-all resize-none"
              placeholder="e.g. Dress code, gate closing time, items not allowed..."
            />
          </div>

          <div>
            <label htmlFor="state" className="block text-sm font-semibold text-gray-700 mb-2">State</label>
            <select
              id="state"
              value={formData.state}
              onChange={(e) => setFormData({ ...formData, state: e.target.value })}
              className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-100 px-5 py-3.5 text-sm shadow-sm transition-all"
            >
              <option value="">Select State</option>
              {Object.entries(ngStates).map(([code, label]) => (
                <option key={code} value={code}>{label}</option>
              ))}
            </select>
          </div>

          <div>
            <label htmlFor="venue" className="block text-sm font-semibold text-gray-700 mb-2">Venue / Location</label>
            <input
              id="venue"
              type="text"
              value={formData.venue}
              onChange={(e) => setFormData({ ...formData, venue: e.target.value })}
              className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
              placeholder="e.g. Eko Hotel, Victoria Island"
            />
          </div>
        </div>

        <div className="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-purple-200 space-y-6">
          <h2 className="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Flyers (Multiple)</h2>
          <p className="text-xs text-gray-500">Upload multiple flyers. They will auto-rotate every 5 seconds on the event page.</p>
          
          {currentImage && (
            <div className="mb-4">
              <p className="text-sm font-medium text-gray-700 mb-2">Current Main Flyer:</p>
              <img src={currentImage} alt="Current flyer" className="max-w-full h-auto rounded-lg" />
            </div>
          )}
          
          <div>
            <label htmlFor="image" className="block text-sm font-semibold text-gray-700 mb-2">Main Flyer</label>
            <input
              id="image"
              type="file"
              accept="image/*"
              onChange={(e) => setFormData({ ...formData, image: e.target.files[0] })}
              className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
            />
          </div>
          
          <div>
            <label htmlFor="gallery" className="block text-sm font-semibold text-gray-700 mb-2">Additional Flyers (Optional)</label>
            <input
              id="gallery"
              type="file"
              accept="image/*"
              multiple
              onChange={(e) => setFormData({ ...formData, gallery: Array.from(e.target.files) })}
              className="w-full px-4 py-3 rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
            />
            <p className="text-xs text-gray-500 mt-1">Select multiple images to add to the gallery</p>
          </div>
        </div>

        <div className="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-purple-200 space-y-6">
          <h2 className="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Event Date & Media</h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label htmlFor="starts_at" className="block text-sm font-semibold text-gray-700 mb-2">Event Date & Time</label>
              <input
                id="starts_at"
                type="datetime-local"
                value={formData.starts_at}
                onChange={(e) => setFormData({ ...formData, starts_at: e.target.value })}
                className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
              />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-purple-200 space-y-6">
          <h2 className="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Tickets & Pricing</h2>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label htmlFor="price" className="block text-sm font-semibold text-gray-700 mb-2">Ticket Price (₦)</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                  <span className="text-gray-500 text-sm font-medium">₦</span>
                </div>
                <input
                  id="price"
                  type="number"
                  value={formData.price || ''}
                  onChange={(e) => setFormData({ ...formData, price: e.target.value ? parseFloat(e.target.value) : '' })}
                  min="0"
                  step="100"
                  className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pl-10 pr-5 py-3.5 text-sm shadow-sm transition-all"
                  placeholder="0"
                />
              </div>
            </div>
            <div>
              <label htmlFor="capacity" className="block text-sm font-semibold text-gray-700 mb-2">Capacity</label>
              <input
                id="capacity"
                type="number"
                value={formData.capacity}
                onChange={(e) => setFormData({ ...formData, capacity: e.target.value })}
                min="1"
                className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
                placeholder="500"
              />
            </div>
          </div>

          <div className="pt-4 border-t border-gray-100">
            <label className="inline-flex items-start gap-3 cursor-pointer">
              <input
                type="checkbox"
                checked={formData.pass_fees_to_buyer}
                onChange={(e) => setFormData({ ...formData, pass_fees_to_buyer: e.target.checked })}
                className="mt-1 w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
              />
              <div>
                <span className="block text-sm font-semibold text-gray-700">Pass ticket fee to buyers</span>
                <span className="block text-xs text-gray-500 mt-1">If enabled, the 10% + ₦100 platform service fee will be added to the ticket price paid by the customer. Otherwise, the fee will be deducted from your payout.</span>
              </div>
            </label>
          </div>
        </div>

        <div className="bg-white rounded-2xl p-8 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-purple-200 space-y-6">
          <h2 className="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Custom URL</h2>

          <div className="pt-4 border-t border-gray-100">
            <label className="inline-flex items-start gap-3 cursor-pointer">
              <input
                type="checkbox"
                checked={formData.use_custom_slug}
                onChange={(e) => setFormData({ ...formData, use_custom_slug: e.target.checked })}
                className="mt-1 w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500"
              />
              <div>
                <span className="block text-sm font-semibold text-gray-700">Use custom URL slug</span>
                <span className="block text-xs text-gray-500 mt-1">Enable this to use a custom URL instead of the default event ID URL</span>
              </div>
            </label>
          </div>

          {formData.use_custom_slug && (
            <div>
              <label htmlFor="custom_slug" className="block text-sm font-semibold text-gray-700 mb-2">Custom Slug</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                  <span className="text-gray-500 text-sm font-medium">twodawn.com.ng/event/</span>
                </div>
                <input
                  id="custom_slug"
                  type="text"
                  value={formData.custom_slug}
                  onChange={(e) => setFormData({ ...formData, custom_slug: e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/(^-|-$)/g, '') })}
                  className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-100 pl-44 pr-5 py-3.5 text-sm shadow-sm transition-all"
                  placeholder="your-custom-slug"
                />
              </div>
              <p className="text-xs text-gray-500 mt-2">URL will be: twodawn.com.ng/event/{formData.custom_slug || 'your-custom-slug'}</p>
            </div>
          )}
        </div>

        <div className="flex gap-5 pt-6">
          <button
            type="button"
            onClick={() => navigate(`/organizer/events/${id}`)}
            className="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-200 bg-white rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-all"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={submitting}
            className="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-900 rounded-xl shadow-sm text-sm font-bold text-gray-900 bg-white hover:bg-gray-50 focus:outline-none transition-all disabled:opacity-50"
          >
            {submitting ? 'Updating...' : 'Update Event'}
          </button>
        </div>
      </form>

      <style>{`
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  );
}

export default EditEvent;
