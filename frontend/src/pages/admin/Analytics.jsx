import { BarChart3 } from 'lucide-react';

function AdminAnalytics() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-white">Analytics</h1>
        <p className="text-gray-400 mt-1">Detailed platform analytics and insights</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="text-center py-12">
          <BarChart3 className="w-12 h-12 mx-auto text-gray-500 mb-4" />
          <p className="text-gray-500">Analytics dashboard coming soon</p>
        </div>
      </div>
    </div>
  );
}

export default AdminAnalytics;
