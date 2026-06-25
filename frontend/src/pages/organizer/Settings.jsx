import { useEffect, useState } from 'react';
import { User, MessageCircle, AtSign, CheckCircle, AlertCircle, Lock, Camera, Upload, Mail } from 'lucide-react';
import api from '../../services/api';

function OrganizerSettings() {
  const [formData, setFormData] = useState({
    name: '',
    instagram_handle: '',
    whatsapp_number: '',
    twitter_handle: '',
    profile_picture: '',
  });
  const [username, setUsername] = useState('');
  const [accountEmail, setAccountEmail] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);
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
      setAccountEmail(s.email || '');
      setFormData({
        name: s.name || '',
        instagram_handle: s.instagram_handle || '',
        whatsapp_number: s.whatsapp_number || '',
        twitter_handle: s.twitter_handle || '',
        profile_picture: s.profile_picture || '',
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

  const handleImageUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
      setErrors(['Please select an image file.']);
      return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      setErrors(['Image must be less than 5MB.']);
      return;
    }

    setUploading(true);
    setErrors([]);

    try {
      const formData = new FormData();
      formData.append('profile_picture', file);

      const response = await api.post('/organizer/settings/profile-picture', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      setFormData(prev => ({ ...prev, profile_picture: response.data.profile_picture }));
      setSuccessMessage('Profile picture updated successfully!');
      setTimeout(() => setSuccessMessage(''), 4000);
    } catch (err) {
      console.error('Failed to upload profile picture', err);
      setErrors([err.response?.data?.message || 'Failed to upload profile picture.']);
    } finally {
      setUploading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setErrors([]);
    setSuccessMessage('');
    try {
      const response = await api.patch('/organizer/settings', formData);
      setSuccessMessage('Settings updated successfully!');
      // Update form data with response
      const s = response.data.settings || {};
      setFormData({
        name: s.name || '',
        instagram_handle: s.instagram_handle || '',
        whatsapp_number: s.whatsapp_number || '',
        twitter_handle: s.twitter_handle || '',
        profile_picture: s.profile_picture || '',
      });
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

          {/* Profile Picture */}
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-3">
              <span className="flex items-center gap-2">
                <Camera className="w-4 h-4 text-purple-500" />
                Profile Picture
              </span>
            </label>
            <div className="flex items-center gap-4">
              <div className="relative w-20 h-20 rounded-full overflow-hidden bg-gray-100 border-2 border-gray-200">
                {formData.profile_picture ? (
                  <img
                    src={formData.profile_picture}
                    alt="Profile"
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-100 to-purple-50">
                    <User className="w-8 h-8 text-purple-400" />
                  </div>
                )}
                {uploading && (
                  <div className="absolute inset-0 bg-black/50 flex items-center justify-center">
                    <div className="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  </div>
                )}
              </div>
              <div className="flex-1">
                <input
                  type="file"
                  id="profile_picture"
                  accept="image/*"
                  onChange={handleImageUpload}
                  disabled={uploading}
                  className="hidden"
                />
                <label
                  htmlFor="profile_picture"
                  className={`inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition cursor-pointer ${
                    uploading
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-purple-50 text-purple-700 hover:bg-purple-100'
                  }`}
                >
                  <Upload className="w-4 h-4" />
                  {uploading ? 'Uploading...' : 'Upload New'}
                </label>
                <p className="text-xs text-gray-400 mt-1.5">JPG, PNG or GIF. Max 5MB.</p>
              </div>
            </div>
          </div>

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

          {/* Account Email — read-only */}
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              <span className="flex items-center gap-2">
                <Mail className="w-4 h-4 text-purple-500" />
                Account Email
              </span>
            </label>
            <div className="flex rounded-xl shadow-sm overflow-hidden border border-gray-200 bg-gray-50">
              <input
                type="email"
                value={accountEmail}
                readOnly
                className="w-full px-4 py-3 bg-gray-50 text-gray-500 text-sm cursor-not-allowed"
              />
              <span className="inline-flex items-center px-3 text-xs text-gray-400 bg-gray-50 border-l border-gray-200 whitespace-nowrap">
                <Lock className="w-4 h-4 mr-1" />
                Read only
              </span>
            </div>
            <p className="text-xs text-gray-400 mt-1.5">Your login email cannot be changed here.</p>
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
