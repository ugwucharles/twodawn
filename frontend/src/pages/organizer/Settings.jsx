import { useEffect, useState } from 'react';
import axios from 'axios';
import { Settings as SettingsIcon, MessageCircle } from 'lucide-react';

function OrganizerSettings() {
  const [formData, setFormData] = useState({
    instagram_handle: '',
    whatsapp_number: '',
    twitter_handle: ''
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');
  const [errors, setErrors] = useState([]);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      const response = await axios.get('/organizer/settings');
      setFormData(response.data.settings || {
        instagram_handle: '',
        whatsapp_number: '',
        twitter_handle: ''
      });
      setLoading(false);
    } catch (err) {
      console.error('Failed to load settings', err);
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setErrors([]);
    try {
      await axios.patch('/organizer/settings', formData);
      setSuccessMessage('Settings updated successfully!');
      setTimeout(() => setSuccessMessage(''), 5000);
    } catch (err) {
      console.error('Failed to update settings', err);
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        setErrors(['Failed to update settings']);
      }
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="text-center py-12">Loading settings...</div>;
  }

  return (
    <div className="max-w-4xl mx-auto py-8 px-6 animate-fade-in">
      {/* Header */}
      <div className="mb-10">
        <h1 className="text-3xl font-black text-gray-900">Settings</h1>
        <p className="text-gray-500 text-sm mt-1">Manage your social media links</p>
      </div>

      {successMessage && (
        <div className="mb-8 p-5 bg-green-50 text-green-700 rounded-2xl text-sm border border-green-200 font-medium">
          {successMessage}
        </div>
      )}

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
        <div className="bg-white rounded-2xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-purple-200 space-y-6">
          <h2 className="text-base font-bold text-gray-900 pb-2 border-b border-gray-100">Social Media Links</h2>
          
          <p className="text-sm text-gray-600">
            These links will be displayed on your event pages so attendees can contact you.
          </p>

          <div>
            <label htmlFor="instagram_handle" className="block text-sm font-semibold text-gray-700 mb-2">
              Instagram Handle
            </label>
            <div className="relative">
              <span className="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-500 text-sm">@</span>
              <input
                id="instagram_handle"
                type="text"
                value={formData.instagram_handle}
                onChange={(e) => setFormData({ ...formData, instagram_handle: e.target.value })}
                className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pl-10 pr-5 py-3.5 text-sm shadow-sm transition-all"
                placeholder="username"
              />
            </div>
            <p className="text-xs text-gray-500 mt-2">Enter your Instagram username without the @ symbol</p>
          </div>

          <div>
            <label htmlFor="whatsapp_number" className="block text-sm font-semibold text-gray-700 mb-2">
              <MessageCircle className="w-5 h-5 inline-block mr-2" />
              WhatsApp Number
            </label>
            <input
              id="whatsapp_number"
              type="text"
              value={formData.whatsapp_number}
              onChange={(e) => setFormData({ ...formData, whatsapp_number: e.target.value })}
              className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-5 py-3.5 text-sm shadow-sm transition-all"
              placeholder="e.g. 2348012345678"
            />
            <p className="text-xs text-gray-500 mt-2">Enter with country code (e.g., 234 for Nigeria)</p>
          </div>

          <div>
            <label htmlFor="twitter_handle" className="block text-sm font-semibold text-gray-700 mb-2">
              Twitter/X Handle
            </label>
            <div className="relative">
              <span className="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-500 text-sm">@</span>
              <input
                id="twitter_handle"
                type="text"
                value={formData.twitter_handle}
                onChange={(e) => setFormData({ ...formData, twitter_handle: e.target.value })}
                className="block w-full rounded-xl border border-gray-200 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pl-10 pr-5 py-3.5 text-sm shadow-sm transition-all"
                placeholder="username"
              />
            </div>
            <p className="text-xs text-gray-500 mt-2">Enter your Twitter/X username without the @ symbol</p>
          </div>
        </div>

        <div className="flex gap-5 pt-6">
          <a
            href="/organizer/dashboard"
            className="flex-1 flex justify-center items-center py-4 px-6 border-2 border-gray-200 bg-white rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-all"
          >
            Cancel
          </a>
          <button
            type="submit"
            disabled={saving}
            className="flex-1 flex justify-center items-center py-4 px-6 border-2 border-blue-500 bg-blue-500 rounded-xl shadow-sm text-sm font-bold text-white hover:bg-blue-600 hover:border-blue-600 focus:outline-none transition-all disabled:opacity-50"
          >
            {saving ? 'Saving...' : 'Save Settings'}
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

export default OrganizerSettings;
