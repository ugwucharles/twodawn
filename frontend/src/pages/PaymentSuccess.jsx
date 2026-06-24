import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getOrder } from '../services/checkout';
import Header from '../components/Header';
import Footer from '../components/Footer';
import QRCode from 'qrcode.react';

function PaymentSuccess() {
  const { reference } = useParams();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    console.log('PaymentSuccess component mounted');
    console.log('Reference from URL:', reference);
    
    const fetch = async () => {
      try {
        console.log('Fetching order for reference:', reference);
        const res = await getOrder(reference);
        console.log('Order response:', res);
        setOrder(res.order);
        setLoading(false);
      } catch (err) {
        console.error('Failed to load order:', err);
        setError(`Failed to load order: ${err.message || 'Unknown error'}`);
        setLoading(false);
      }
    };
    
    if (reference) {
      fetch();
    } else {
      setError('No reference provided in URL');
      setLoading(false);
    }
  }, [reference]);

  const downloadQR = () => {
    const canvas = document.getElementById('qrCanvas');
    const pngUrl = canvas.toDataURL('image/png').replace('image/png', 'image/octet-stream');
    const downloadLink = document.createElement('a');
    downloadLink.href = pngUrl;
    downloadLink.download = `ticket-${reference}.png`;
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12">
            <div className="w-10 h-10 border-4 border-[#8b5cf6] border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <div>Loading ticket...</div>
            <div className="text-sm text-gray-500 mt-2">Reference: {reference}</div>
          </div>
        </main>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-white flex flex-col">
        <Header />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center py-12 px-4">
            <div className="text-red-600 mb-4">{error}</div>
            <div className="text-sm text-gray-500">Reference: {reference}</div>
            <button
              onClick={() => window.location.reload()}
              className="mt-4 bg-[#8b5cf6] text-white px-6 py-2 rounded-lg hover:bg-[#7c3aed] transition"
            >
              Retry
            </button>
          </div>
        </main>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-white flex flex-col">
      <Header />
      <main className="flex-1 flex flex-col items-center justify-center py-8">
        {/* Debug Info */}
        <div className="fixed top-20 right-4 bg-gray-100 p-4 rounded-lg text-xs max-w-xs z-50">
          <div className="font-bold mb-2">Debug Info:</div>
          <div>Reference: {reference || 'none'}</div>
          <div>Loading: {loading ? 'yes' : 'no'}</div>
          <div>Error: {error || 'none'}</div>
          <div>Order: {order ? 'loaded' : 'null'}</div>
          {order && (
            <div className="mt-2">
              <div>Order ID: {order.id}</div>
              <div>Status: {order.status}</div>
            </div>
          )}
        </div>

        <h1 className="text-3xl font-bold text-gray-900 mb-6">Your Ticket is Ready</h1>
        <div className="bg-white p-6 rounded-xl shadow-lg">
          <QRCode
            id="qrCanvas"
            value={order?.qr_data || reference}
            size={256}
            level="H"
            includeMargin={true}
          />
        </div>
        <button
          onClick={downloadQR}
          className="mt-6 bg-[#8b5cf6] text-white px-6 py-2 rounded-lg hover:bg-[#7c3aed] transition"
        >
          Download QR Code
        </button>
        <p className="mt-4 text-sm text-gray-600">
          Remember your email in case you lose the QR code or screenshot it.
        </p>
        <button
          onClick={() => navigate('/')}
          className="mt-8 bg-gray-200 text-gray-800 px-4 py-2 rounded"
        >
          Back to Home
        </button>
      </main>
      <Footer />
    </div>
  );
}

export default PaymentSuccess;
