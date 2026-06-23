import { useEffect, useState } from 'react';
import { User, MessageCircle, Link, AtSign, CheckCircle, AlertCircle, Lock } from 'lucide-react';
import api from '../../services/api';

function OrganizerSettings() {
  const [formData, setFormData] = useState({
    name: '',
    instagram_handle: '',
    whatsapp_number: '',
    twitter_handle: '',
  });
  const [username, setUsername] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');
  const [errors, setErrors] = useState([]);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      const response = await api.get('/organizer/settings');
      const s = response.data.settings || {};
      setUsername(s.username || '');
      setFormData({
        name: s.name || '',
        instagram_handle: s.instagram_handle || '',
        whatsapp_number: s.whatsapp_number || '',
        twitter_handle: s.twitter_handle || '',
      });
    } catch (err) {
      console.error('Failed to load settings', err);
      setErrors(['Failed to load settings. Please refresh.']);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    setErrors([]);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setErrors([]);
    setSuccessMessage('');
    try {
      await api.patch('/organizer/settings', formData);
      setSuccessMessage('Settings updated successfully!');
      setTimeout(() => setSuccessMessage(''), 4000);
    } catch (err) {
      console.error('Failed to update settings', err);
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors);
      } else {
        setErrors([err.response?.data?.message || 'Failed to update settings.']);
      }
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-24">
        <div className="w-7 h-7 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto py-8 px-4 sm:px-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-2xl font-black text-gray-900">Organizer Settings</h1>
        <p className="text-gray-500 text-sm mt-1">Manage your public profile and contact info</p>
      </div>

      {/* Success */}
      {successMessage && (
        <div className="mb-6 flex items-center gap-3 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200 text-sm font-medium">
          <CheckCircle className="w-5 h-5 flex-shrink-0" />
          {successMessage}
        </div>
      )}

      {/* Errors */}
      {errors.length > 0 && (
        <div className="mb-6 flex items-start gap-3 p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 text-sm">
          <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5" />
          <ul className="space-y-1">
            {errors.map((err, i) => <li key={i}>{err}</li>)}
          </ul>
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">

        {/* Profile Card */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
          <h2 className="text-sm font-bold text-gray-500 uppercase tracking-wider pb-1 border-b border-gray-100">
            Profile
          </h2>

          {/* Username — read-only */}
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              <span className="flex items-center gap-2">
                <AtSign className="w-4 h-4 text-purple-500" />
                Username / Handle
              </span>
            </label>
            <div className="flex rounded-xl shadow-sm overflow-hidden border border-gray-200 bg-gray-50">
              <span className="inline-flex items-center px-3 text-gray-400 text-sm bg-gray-50 border-r border-gray-200">
                twodawn.com.ng/
              </span>
              <input
                type="text"
                value={username}
                readOnly
                className="w-full px-4 py-3 bg-gray-50 text-gray-500 text-sm font-semibold cursor-not-allowed"
              />
              <span className="inline-flex items-center px-3 text-xs text-gray-400 bg-gray-50 border-l border-gray-200 whitespace-nowrap flex items-center">
                <Lock className="w-4 h-4 mr-1" />
                Permanent
              </span>
            </div>
            <p className="text-xs text-gray-400 mt-1.5">Your username cannot be changed after onboarding.</p>
          </div>

          {/* Display Name */}
          <div>
            <label htmlFor="name" className="block text-sm font-semibold text-gray-700 mb-1.5">
              <span className="flex items-center gap-2">
                <User className="w-4 h-4 text-purple-500" />
                Brand / Display Name
              </span>
            </label>
            <input
              id="name"
              type="text"
              value={formData.name}
              onChange={(e) => handleChange('name', e.target.value)}
              className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition"
              placeholder="e.g. 2DAWN Events"
            />
            <p className="text-xs text-gray-400 mt-1.5">This appears as "Hosted by [name]" on your event pages.</p>
          </div>
        </div>

        {/* Contact / Social Card */}
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
          <h2 className="text-sm font-bold text-gray-500 uppercase tracking-wider pb-1 border-b border-gray-100">
            Contact &amp; Social
          </h2>
          <p className="text-sm text-gray-500">These links appear on your event pages so attendees can reach you.</p>

          {/* Instagram */}
          <div>
            <label htmlFor="instagram" className="block text-sm font-semibold text-gray-700 mb-1.5">
              <span className="flex items-center gap-2">
                <span className="text-pink-500 font-bold text-sm">IG</span>
                Instagram
              </span>
            </label>
            <div className="flex rounded-xl overflow-hidden border border-gray-200 shadow-sm">
              <span className="inline-flex items-center px-3 text-gray-400 text-sm bg-gray-50 border-r border-gray-200">
                instagram.com/
              </span>
              <input
                id="instagram"
                type="text"
                value={formData.instagram_handle}
                onChange={(e) => handleChange('instagram_handle', e.target.value)}
                className="w-full px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                placeholder="username"
              />
            </div>
          </div>

          {/* Twitter/X */}
          <div>
            <label htmlFor="twitter" className="block text-sm font-semibold text-gray-700 mb-1.5">
              <span className="flex items-center gap-2">
                <span className="text-sky-500 font-bold text-sm">𝕏</span>
                Twitter / X
              </span>
            </label>
            <div className="flex rounded-xl overflow-hidden border border-gray-200 shadow-sm">
              <span className="inline-flex items-center px-3 text-gray-400 text-sm bg-gray-50 border-r border-gray-200">
                x.com/
              </span>
              <input
                id="twitter"
                type="text"
                value={formData.twitter_handle}
                onChange={(e) => handleChange('twitter_handle', e.target.value)}
                className="w-full px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400 transition"
                placeholder="username"
              />
            </div>
          </div>

          {/* WhatsApp */}
          <div>
            <label htmlFor="whatsapp" className="block text-sm font-semibold text-gray-700 mb-1.5">
              <span className="flex items-center gap-2">
                <MessageCircle className="w-4 h-4 text-green-500" />
                WhatsApp Number
              </span>
            </label>
            <input
              id="whatsapp"
              type="tel"
              value={formData.whatsapp_number}
              onChange={(e) => handleChange('whatsapp_number', e.target.value)}
              className="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition"
              placeholder="+2348012345678"
            />
            <p className="text-xs text-gray-400 mt-1.5">Include country code (e.g. +234 for Nigeria)</p>
          </div>
        </div>

        {/* Actions */}
        <div className="flex gap-4 pt-2">
          <a
            href="/organizer/dashboard"
            className="flex-1 flex justify-center items-center py-3.5 px-6 border-2 border-gray-200 bg-white rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition"
          >
            Cancel
          </a>
          <button
            type="submit"
            disabled={saving}
            className="flex-1 flex justify-center items-center py-3.5 px-6 bg-[#8b5cf6] rounded-xl text-sm font-bold text-white hover:bg-[#7c3aed] focus:outline-none transition disabled:opacity-50"
          >
            {saving ? (
              <span className="flex items-center gap-2">
                <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                Saving...
              </span>
            ) : 'Save Settings'}
          </button>
        </div>
      </form>
    </div>
  );
}

export default OrganizerSettings;
