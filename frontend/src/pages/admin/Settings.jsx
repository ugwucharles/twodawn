import { Settings } from 'lucide-react';

function AdminSettings() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-white">Settings</h1>
        <p className="text-gray-400 mt-1">Platform configuration and settings</p>
      </div>

      <div className="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div className="text-center py-12">
          <Settings className="w-12 h-12 mx-auto text-gray-500 mb-4" />
          <p className="text-gray-500">Settings panel coming soon</p>
        </div>
      </div>
    </div>
  );
}

export default AdminSettings;
