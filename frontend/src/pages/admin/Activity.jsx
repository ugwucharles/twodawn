import { useState, useEffect } from 'react';
import axios from 'axios';
import { Activity } from 'lucide-react';

function AdminActivity() {
  const [activity, setActivity] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchActivity();
  }, []);

  const fetchActivity = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_BACKEND_URL}/admin/activity`);
      setActivity(response.data.activity || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load activity', err);
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
        <h1 className="text-3xl font-bold text-white">Live Activity</h1>
        <p className="text-gray-400 mt-1">Real-time activity across the platform</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="space-y-4">
          {activity.length > 0 ? (
            activity.map((item, index) => (
              <div key={index} className="flex items-start space-x-3 p-4 bg-gray-800/50 rounded-lg">
                <div className="w-2 h-2 mt-2 rounded-full bg-purple-500"></div>
                <div className="flex-1">
                  <p className="text-sm text-gray-300">{item.action}</p>
                  <p className="text-xs text-gray-500 mt-1">
                    {new Date(item.created_at).toLocaleString()}
                  </p>
                  {item.entity_type && (
                    <p className="text-xs text-gray-500">
                      {item.entity_type} {item.entity_id}
                    </p>
                  )}
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-12">
              <Activity className="w-12 h-12 mx-auto text-gray-500 mb-4" />
              <p className="text-gray-500">No activity recorded yet</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default AdminActivity;
