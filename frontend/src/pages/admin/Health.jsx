import { useState, useEffect } from 'react';
import axios from 'axios';
import { Database, Activity, CheckCircle, XCircle, AlertCircle } from 'lucide-react';

function AdminHealth() {
  const [health, setHealth] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchHealth();
  }, []);

  const fetchHealth = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_BACKEND_URL}/admin/health`);
      setHealth(response.data.health);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load health status', err);
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

  const getStatusIcon = (status) => {
    if (status === 'healthy') return <CheckCircle className="w-5 h-5 text-green-400" />;
    if (status === 'unhealthy') return <XCircle className="w-5 h-5 text-red-400" />;
    return <AlertCircle className="w-5 h-5 text-yellow-400" />;
  };

  const getStatusColor = (status) => {
    if (status === 'healthy') return 'text-green-400';
    if (status === 'unhealthy') return 'text-red-400';
    return 'text-yellow-400';
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-white">System Health</h1>
        <p className="text-gray-400 mt-1">Monitor platform system status</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-400">Database</p>
              <p className={`text-lg font-semibold mt-1 ${getStatusColor(health?.database)}`}>
                {health?.database || 'Unknown'}
              </p>
            </div>
            <Database className="w-8 h-8 text-gray-500" />
          </div>
        </div>

        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-400">API</p>
              <p className={`text-lg font-semibold mt-1 ${getStatusColor(health?.api)}`}>
                {health?.api || 'Unknown'}
              </p>
            </div>
            <Activity className="w-8 h-8 text-gray-500" />
          </div>
        </div>

        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-400">Payment Gateway</p>
              <p className={`text-lg font-semibold mt-1 ${getStatusColor(health?.payment_gateway)}`}>
                {health?.payment_gateway || 'Unknown'}
              </p>
            </div>
            <CheckCircle className="w-8 h-8 text-gray-500" />
          </div>
        </div>

        <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm text-gray-400">Email Service</p>
              <p className={`text-lg font-semibold mt-1 ${getStatusColor(health?.email_service)}`}>
                {health?.email_service || 'Unknown'}
              </p>
            </div>
            <AlertCircle className="w-8 h-8 text-gray-500" />
          </div>
        </div>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 className="text-lg font-semibold text-white mb-4">Recent Errors</h2>
        <p className="text-gray-500">Recent errors in the last hour: {health?.recent_errors || 0}</p>
      </div>
    </div>
  );
}

export default AdminHealth;
