import { useState, useEffect } from 'react';
import axios from 'axios';
import { Users } from 'lucide-react';

function AdminUsers() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_BACKEND_URL}/admin/users`);
      setUsers(response.data.users || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load users', err);
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
        <h1 className="text-3xl font-bold text-white">Users</h1>
        <p className="text-gray-400 mt-1">Manage all users on the platform</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="space-y-4">
          {users.length > 0 ? (
            users.map((user, index) => (
              <div key={index} className="flex items-center justify-between p-4 bg-gray-800/50 rounded-lg">
                <div>
                  <p className="text-sm font-medium text-white">{user.name}</p>
                  <p className="text-xs text-gray-500 mt-1">{user.email}</p>
                </div>
                <div className="text-right">
                  <p className="text-sm text-gray-300">{user.orders_count || 0} orders</p>
                  <p className="text-xs text-gray-500">{user.role}</p>
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-12">
              <Users className="w-12 h-12 mx-auto text-gray-500 mb-4" />
              <p className="text-gray-500">No users found</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default AdminUsers;
