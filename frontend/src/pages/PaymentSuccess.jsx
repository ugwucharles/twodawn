import { useEffect, useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { getOrder } from '../services/checkout';
import Header from '../components/Header';
import Footer from '../components/Footer';
import { QRCodeSVG } from 'qrcode.react';

function PaymentSuccess() {
  const [searchParams] = useSearchParams();
  const reference = searchParams.get('reference');
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    console.log('PaymentSuccess component mounted');
    console.log('Reference from URL:', reference);
    console.log('Search params:', Object.fromEntries(searchParams));
    
    const fetch = async () => {
      try {
        console.log('Fetching order for reference:', reference);
        const res = await getOrder(reference);
        console.log('Order response:', res);
        setOrder(res.order);
        setLoading(false);
      } catch (err) {
        console.error('Failed to load order:', err);
        const errorMessage = `Failed to load order: ${err.message || 'Unknown error'}`;
        setError(errorMessage);
        
        // Save error to localStorage for debug page
        localStorage.setItem('payment_error', JSON.stringify({
          message: err.message,
          response: err.response?.data,
          status: err.response?.status,
          reference: reference,
          timestamp: new Date().toISOString()
        }));
        
        setLoading(false);
      }
    };
    
    if (reference) {
      fetch();
    } else {
      const noRefError = 'No reference provided in URL';
      setError(noRefError);
      localStorage.setItem('payment_error', JSON.stringify({
        message: noRefError,
        timestamp: new Date().toISOString()
      }));
      setLoading(false);
    }
  }, [reference]);

  const downloadQR = () => {
    const svgElement = document.getElementById('qrCanvas');
    const svgData = new XMLSerializer().serializeToString(svgElement);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = () => {
      canvas.width = img.width;
      canvas.height = img.height;
      ctx.drawImage(img, 0, 0);
      const pngUrl = canvas.toDataURL('image/png').replace('image/png', 'image/octet-stream');
      const downloadLink = document.createElement('a');
      downloadLink.href = pngUrl;
      downloadLink.download = `ticket-${reference}.png`;
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
    };
    
    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
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

        <h1 className="text-3xl font-bold text-gray-900 mb-6">Your Ticket is Ready</h1>
        <div className="bg-white p-6 rounded-xl shadow-lg relative">
          <QRCodeSVG
            id="qrCanvas"
            value={order?.qr_data || reference}
            size={256}
            level="H"
            includeMargin={true}
          />
          <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-2 rounded-lg shadow-md">
            <img src="/logo.svg" alt="2DAWN" className="w-12 h-12" />
          </div>
        </div>
        <button
          onClick={downloadQR}
          className="mt-6 bg-[#8b5cf6] text-white px-6 py-2 rounded-lg hover:bg-[#7c3aed] transition"
        >
          Download QR Code
        </button>
        <p className="mt-4 text-sm text-gray-600">
          💡 Tip: Save a screenshot of this QR code for easy access at the event.
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
