import { useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';

function PaymentSuccess() {
  const [searchParams] = useSearchParams();
  const reference = searchParams.get('reference');
  const navigate = useNavigate();

  useEffect(() => {
    if (reference) {
      navigate(`/orders/${encodeURIComponent(reference)}`, { replace: true });
      return;
    }

    navigate('/events?payment=missing-reference', { replace: true });
  }, [navigate, reference]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-white">
      <div className="text-sm text-gray-600">Redirecting...</div>
    </div>
  );
}

export default PaymentSuccess;
