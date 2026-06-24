import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../services/api';
import { ArrowLeft, ExternalLink, Edit, AlertTriangle } from 'lucide-react';

function EventDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [event, setEvent] = useState(null);
  const [stats, setStats] = useState({ totalSold: 0, totalRevenue: 0 });
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchEventDetails();
  }, [id]);

  const fetchEventDetails = async () => {
    try {
      const response = await api.get(`/organizer/events/${id}`);
      console.log('Event details response:', response.data);
      setEvent(response.data.event);
      setStats(response.data.stats);
      setOrders(response.data.orders || []);
      setLoading(false);
    } catch (err) {
      console.error('Failed to load event details', err);
      console.error('Error response:', err.response?.data);
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="text-center py-12">Loading event details...</div>;
  }

  if (!event) {
    return <div className="text-center py-12">Event not found</div>;
  }

  return (
    <div className="max-w-7xl mx-auto mt-2 animate-fade-in">
      {/* Header */}
      <div className="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex items-start gap-4">
          <button
            onClick={() => navigate('/organizer/dashboard')}
            className="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-500 hover:text-gray-700 shadow-sm transition-colors shrink-0"
          >
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{event.title}</h1>
            <p className="text-gray-500 text-sm mt-1">{event.venue} — {new Date(event.starts_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })}</p>
          </div>
        </div>

        <div className="flex items-center gap-4">
          {event.is_published ? (
            <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Live</span>
          ) : (
            <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Draft</span>
          )}
          <a
            href={`/events/${event.id}`}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-semibold bg-blue-50 hover:bg-blue-100 rounded-xl px-4 py-2 transition-colors"
          >
            <ExternalLink className="w-4 h-4" />
            View Public Page
          </a>
          <button
            onClick={() => navigate(`/organizer/events/${id}/edit`)}
            className="inline-flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 font-semibold bg-white border border-gray-200 hover:bg-gray-50 rounded-xl px-4 py-2 shadow-sm transition-colors"
          >
            <Edit className="w-4 h-4" />
            Edit Event
          </button>
        </div>
      </div>

      {/* Stats Row */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div className="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
          <p className="text-sm font-medium text-gray-500 mb-2">Tickets Sold</p>
          <div className="flex items-end gap-2">
            <p className="text-3xl font-bold text-gray-900">{stats.totalSold}</p>
            {event.capacity && (
              <p className="text-sm text-gray-400 mb-1">/ {event.capacity}</p>
            )}
          </div>
        </div>
        <div className="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
          <p className="text-sm font-medium text-gray-500 mb-2">Ticket Types</p>
          {event.ticket_types && event.ticket_types.length > 0 ? (
            <div className="space-y-1">
              {event.ticket_types.map((ticket, idx) => (
                <div key={idx} className="flex justify-between items-center">
                  <span className="text-sm font-medium text-gray-900">{ticket.name}</span>
                  <span className="text-sm font-bold text-gray-900">
                    {ticket.price > 0 ? `₦${ticket.price.toLocaleString()}` : 'Free'}
                  </span>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-3xl font-bold text-gray-900">
              {event.price > 0 ? `₦${event.price.toFixed(2)}` : 'Free'}
            </p>
          )}
        </div>
        <div className="bg-white rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)]">
          <p className="text-sm font-medium text-gray-500 mb-2">Total Revenue</p>
          <p className="text-3xl font-bold text-gray-900 mb-2">₦{(stats.totalRevenue / 100).toFixed(2)}</p>
        </div>
      </div>

      {/* MUST KNOW! Info Box */}
      {event.must_know && (
        <div className="mb-8 bg-gradient-to-r from-purple-50 to-purple-200 border border-purple-200 rounded-2xl p-6 shadow-[0_2px_10px_-3px_rgba(147,51,234,0.15)]">
          <div className="flex items-start gap-3">
            <div className="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
              <AlertTriangle className="w-5 h-5 text-purple-600" />
            </div>
            <div className="flex-1">
              <h3 className="text-base font-extrabold text-purple-800 tracking-wide mb-1">MUST KNOW!</h3>
              <p className="text-sm text-purple-900/80 leading-relaxed whitespace-pre-line">{event.must_know}</p>
            </div>
          </div>
        </div>
      )}

      {/* Attendees Table */}
      <div className="bg-white rounded-2xl p-0 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden">
        <div className="p-6 flex justify-between items-center border-b border-gray-100">
          <h2 className="text-lg font-bold text-gray-900">Attendees</h2>
          <span className="text-sm font-medium text-gray-500 bg-gray-50 px-3 py-1 rounded-lg">{orders.length} total orders</span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left">
            <thead className="text-xs text-gray-500 uppercase bg-gray-50">
              <tr>
                <th className="px-6 py-4 font-semibold">Name</th>
                <th className="px-6 py-4 font-semibold">Email</th>
                <th className="px-6 py-4 font-semibold text-center">Qty</th>
                <th className="px-6 py-4 font-semibold text-right">Paid</th>
                <th className="px-6 py-4 font-semibold">Date</th>
              </tr>
            </thead>
            <tbody>
              {orders.length > 0 ? (
                orders.map((order) => (
                  <tr key={order.id} className="bg-white border-b border-gray-50 last:border-0 hover:bg-gray-50">
                    <td className="px-6 py-4 font-medium text-gray-900">{order.buyer_name}</td>
                    <td className="px-6 py-4 text-gray-600">{order.buyer_email}</td>
                    <td className="px-6 py-4 text-center text-gray-700">{order.quantity}</td>
                    <td className="px-6 py-4 text-right font-bold text-gray-900">₦{(order.amount / 100).toFixed(2)}</td>
                    <td className="px-6 py-4 text-gray-500">{new Date(order.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="5" className="px-6 py-12 text-center text-gray-500">No ticket sales yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <style>{`
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      `}</style>
    </div>
  );
}

export default EventDetails;
