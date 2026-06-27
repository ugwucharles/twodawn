import { useState, useEffect } from 'react';
import axios from 'axios';
import { Users, Plus, Edit2, Trash2, Shield, Search } from 'lucide-react';

function AdminOrganizers() {
  const [organizers, setOrganizers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  
  // Modal states
  const [modalOpen, setModalOpen] = useState(false);
  const [editingOrganizer, setEditingOrganizer] = useState(null);
  
  // Form states
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    username: '',
    password: '',
  });

  useEffect(() => {
    fetchOrganizers();
  }, []);

  const fetchOrganizers = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_BACKEND_URL}/ucc/organizers`);
      setOrganizers(response.data.organizers || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load organizers', err);
      setLoading(false);
    }
  };

  const handleDeleteOrganizer = async (organizerId) => {
    if (!window.confirm('Are you sure you want to delete this organizer? This will delete their account.')) return;
    try {
      const res = await axios.delete(`${import.meta.env.VITE_BACKEND_URL}/ucc/organizers/${organizerId}`);
      if (res.data.ok) {
        setOrganizers(organizers.filter(o => o.id !== organizerId));
      }
    } catch (err) {
      console.error('Failed to delete organizer', err);
      alert(err.response?.data?.error || 'Failed to delete organizer');
    }
  };

  const openCreateModal = () => {
    setEditingOrganizer(null);
    setFormData({
      name: '',
      email: '',
      username: '',
      password: '',
    });
    setModalOpen(true);
  };

  const openEditModal = (organizer) => {
    setEditingOrganizer(organizer);
    setFormData({
      name: organizer.name || '',
      email: organizer.email || '',
      username: organizer.username || '',
      password: '', // Leave blank for edit
    });
    setModalOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingOrganizer) {
        // Edit Mode
        const payload = {
          name: formData.name,
          email: formData.email,
          username: formData.username,
        };
        const res = await axios.patch(`${import.meta.env.VITE_BACKEND_URL}/ucc/organizers/${editingOrganizer.id}`, payload);
        if (res.data.ok) {
          fetchOrganizers();
          setModalOpen(false);
        }
      } else {
        // Create Mode
        const res = await axios.post(`${import.meta.env.VITE_BACKEND_URL}/ucc/organizers`, formData);
        if (res.data.ok) {
          fetchOrganizers();
          setModalOpen(false);
        }
      }
    } catch (err) {
      console.error('Failed to save organizer', err);
      alert(err.response?.data?.error || 'Failed to save organizer');
    }
  };

  const filteredOrganizers = organizers.filter(o => 
    o.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    o.email?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    o.username?.toLowerCase().includes(searchTerm.toLowerCase())
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
          <h1 className="text-3xl font-bold text-white">Organizers Management</h1>
          <p className="text-gray-400 mt-1">Manage and audit platform event hosts</p>
        </div>
        <button
          onClick={openCreateModal}
          className="flex items-center space-x-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors"
        >
          <Plus className="w-5 h-5" />
          <span>Add Organizer</span>
        </button>
      </div>

      {/* Search Filter */}
      <div className="relative">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
        <input
          type="text"
          placeholder="Search organizers by name, email, or username..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="w-full pl-10 pr-4 py-2.5 bg-gray-900 border border-gray-800 rounded-lg text-sm text-white placeholder-gray-500 focus:outline-none focus:border-purple-500"
        />
      </div>

      {/* Organizers List */}
      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="space-y-4">
          {filteredOrganizers.length > 0 ? (
            filteredOrganizers.map((org, index) => (
              <div key={index} className="flex flex-col md:flex-row md:items-center justify-between p-4 bg-gray-800/30 border border-gray-800/50 rounded-lg gap-4 hover:border-gray-700 transition-colors">
                <div>
                  <div className="flex items-center space-x-2">
                    <p className="text-sm font-semibold text-white">{org.name}</p>
                    <span className="flex items-center space-x-1 px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 text-[10px] border border-purple-500/20 font-medium">
                      <Shield className="w-2.5 h-2.5" />
                      <span>Host</span>
                    </span>
                  </div>
                  <p className="text-xs text-gray-500 mt-1">@{org.username} • {org.email}</p>
                  <div className="flex space-x-4 mt-2">
                    <span className="text-xs text-gray-400">Events: {org.events_count || 0}</span>
                    <span className="text-xs text-gray-400">Revenue: ₦{((org.total_revenue || 0) / 100).toLocaleString()}</span>
                  </div>
                </div>
                <div className="flex items-center space-x-2">
                  {/* Edit button */}
                  <button
                    onClick={() => openEditModal(org)}
                    className="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors border border-gray-800"
                  >
                    <Edit2 className="w-4 h-4" />
                  </button>

                  {/* Delete button */}
                  <button
                    onClick={() => handleDeleteOrganizer(org.id)}
                    className="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors border border-gray-800"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-12">
              <Users className="w-12 h-12 mx-auto text-gray-500 mb-4" />
              <p className="text-gray-500">No organizers found matching your search</p>
            </div>
          )}
        </div>
      </div>

      {/* Create / Edit Organizer Modal */}
      {modalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="w-full max-w-lg bg-gray-900 border border-gray-800 rounded-xl shadow-2xl p-6 space-y-6">
            <div className="flex items-center justify-between border-b border-gray-800 pb-4">
              <h2 className="text-xl font-bold text-white">{editingOrganizer ? 'Edit Organizer Details' : 'Create New Organizer'}</h2>
              <button onClick={() => setModalOpen(false)} className="text-gray-500 hover:text-white">&times;</button>
            </div>
            
            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-gray-400 uppercase">Organizer Full Name</label>
                <input
                  type="text"
                  required
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  placeholder="e.g. John Doe"
                  className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-400 uppercase">Email Address</label>
                <input
                  type="email"
                  required
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  placeholder="e.g. john@twodawn.com"
                  className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-400 uppercase">Username</label>
                <input
                  type="text"
                  required
                  value={formData.username}
                  onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                  placeholder="e.g. johndoe"
                  className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                />
              </div>

              {!editingOrganizer && (
                <div>
                  <label className="block text-xs font-semibold text-gray-400 uppercase">Access Password</label>
                  <input
                    type="password"
                    required
                    value={formData.password}
                    onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                    placeholder="Enter organizer password"
                    className="w-full mt-1.5 px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500"
                  />
                </div>
              )}

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
                  Save Organizer
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default AdminOrganizers;
