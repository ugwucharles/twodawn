import { useState, useRef, useEffect } from 'react';
import { Scan, Camera, X, Upload, CheckCircle, AlertTriangle, XCircle, Camera as CameraIcon } from 'lucide-react';
import api from '../../services/api';
import jsQR from 'jsqr';

function Scanner() {
  const [stream, setStream] = useState(null);
  const [running, setRunning] = useState(false);
  const [status, setStatus] = useState({ msg: '', kind: 'idle' });
  const [inlineResult, setInlineResult] = useState(null);
  const [modal, setModal] = useState(null);
  const [codeInput, setCodeInput] = useState('');
  const [facingMode, setFacingMode] = useState('environment'); // 'environment' for back, 'user' for front
  const [debugInfo, setDebugInfo] = useState({ qrData: null, apiResponse: null });
  const videoRef = useRef(null);
  const canvasRef = useRef(null);
  const rafRef = useRef(null);

  useEffect(() => {
    return () => {
      stopCamera();
    };
  }, []);

  const setStatusMsg = (msg, kind = 'idle') => {
    setStatus({ msg, kind });
  };

  const showInlineResult = (kind, msg) => {
    setInlineResult({ kind, msg });
  };

  const openModal = (kind, opts = {}) => {
    setModal({ kind, ...opts });
  };

  const closeModal = () => {
    setModal(null);
  };

  const verifyText = async (text) => {
    setStatusMsg('Verifying…', 'scanning');
    try {
      console.log('Sending verification request for:', text);
      const res = await api.post('/organizer/scanner/verify', { code: text });
      const data = res.data;
      console.log('Verification response:', data);
      setDebugInfo(prev => ({ ...prev, apiResponse: data }));

      if (data.valid) {
        setStatusMsg('Approved', 'ok');
        showInlineResult('ok', `${data.buyer?.name} checked in to ${data.event?.title}`);
        openModal('ok', {
          title: '✓ Check-in Approved!',
          sub: data.event?.title || '',
          buyer: data.buyer,
          last: data.last_checkin_at,
        });
      } else if (data.already) {
        setStatusMsg('Already used', 'warn');
        showInlineResult('warn', `Ticket was already used. Buyer: ${data.buyer?.name} (${data.buyer?.email})`);
        openModal('warn', {
          title: 'Already Checked In',
          sub: data.event?.title || '',
          buyer: data.buyer,
          last: data.last_checkin_at,
        });
      } else {
        setStatusMsg('Invalid', 'err');
        const msg = data.message || 'Ticket is not valid.';
        console.log('Invalid ticket reason:', msg);
        showInlineResult('err', msg);
        openModal('err', { title: 'Invalid Ticket', sub: msg });
      }
    } catch (e) {
      console.error('Verification error:', e);
      console.error('Error response:', e.response?.data);
      setDebugInfo(prev => ({ ...prev, apiResponse: { error: e.message, response: e.response?.data } }));
      setStatusMsg('Error', 'err');
      showInlineResult('err', 'Network error.');
    }
  };

  const startCamera = async () => {
    try {
      setStatusMsg('Starting…', 'scanning');
      const s = await navigator.mediaDevices.getUserMedia({ 
        video: { facingMode: facingMode }, 
        audio: false 
      });
      if (!s) throw new Error('No camera found');
      setStream(s);
      if (videoRef.current) {
        videoRef.current.srcObject = s;
        await videoRef.current.play();
      }
      setRunning(true);
      setStatusMsg('Scanning…', 'scanning');
      scanCanvas();
    } catch (e) {
      console.error('Camera start error:', e);
      setStatusMsg('Camera blocked: ' + (e?.message || e), 'err');
      showInlineResult('err', 'Allow camera access, or use the image upload / manual entry.');
    }
  };

  const switchCamera = async () => {
    const wasRunning = running;
    stopCamera();
    setFacingMode(prev => prev === 'environment' ? 'user' : 'environment');
    if (wasRunning) {
      await startCamera();
    }
  };

  const stopCamera = () => {
    setRunning(false);
    if (rafRef.current) {
      cancelAnimationFrame(rafRef.current);
      rafRef.current = null;
    }
    if (videoRef.current) {
      videoRef.current.pause?.();
      videoRef.current.srcObject = null;
    }
    if (stream) {
      stream.getTracks().forEach(t => t.stop());
      setStream(null);
    }
    setStatusMsg('Ready');
  };

  const scanCanvas = () => {
    const tick = () => {
      if (!running) return;
      try {
        if (videoRef.current && canvasRef.current) {
          const canvas = canvasRef.current;
          const ctx = canvas.getContext('2d');
          const W = 480, H = 360;
          canvas.width = W;
          canvas.height = H;
          ctx.drawImage(videoRef.current, 0, 0, W, H);
          
          const imageData = ctx.getImageData(0, 0, W, H);
          const code = jsQR(imageData.data, imageData.width, imageData.height);
          
          if (code) {
            console.log('QR code detected:', code.data);
            console.log('QR code data type:', typeof code.data);
            console.log('QR code data length:', code.data.length);
            setDebugInfo(prev => ({ ...prev, qrData: code.data }));
            verifyText(code.data);
            stopCamera();
            return;
          }
        }
      } catch (_) {}
      rafRef.current = requestAnimationFrame(tick);
    };
    rafRef.current = requestAnimationFrame(tick);
  };

  const handleManualVerify = async () => {
    const v = codeInput.trim();
    if (v) await verifyText(v);
    else showInlineResult('err', 'Please enter a reference code.');
  };

  const handleFileUpload = async (file) => {
    if (!file) return;
    setStatusMsg('Decoding image…', 'scanning');
    try {
      // Note: In production, you'd use a QR library to decode the image
      showInlineResult('err', 'QR code detection requires a library like jsQR');
    } catch (e) {
      setStatusMsg('Error', 'err');
      showInlineResult('err', 'Failed to decode image.');
    }
  };

  return (
    <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
      {/* Header */}
      <div className="mb-8 flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-black text-gray-900 tracking-tight">QR Scanner</h1>
          <p className="text-gray-900 text-sm font-medium mt-1">Scan tickets for your events to check guests in.</p>
        </div>
        <div className="flex items-center gap-2">
          <span className={`inline-block w-2.5 h-2.5 rounded-full transition-colors duration-300 ${
            status.kind === 'scanning' ? 'bg-purple-400 animate-pulse' :
            status.kind === 'ok' ? 'bg-emerald-400' :
            status.kind === 'warn' ? 'bg-amber-400' :
            status.kind === 'err' ? 'bg-red-400' :
            'bg-gray-300'
          }`}></span>
          <span className="ml-2 text-sm text-gray-600">{status.msg}</span>
        </div>
      </div>

      <div className="grid lg:grid-cols-2 gap-6 items-start">
        {/* Camera panel */}
        <div className="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-6 shadow-sm">
          <div className="flex items-center gap-3 mb-4">
            <button
              onClick={startCamera}
              className="px-5 py-2.5 rounded-xl bg-purple-600 text-black border border-black text-sm font-bold hover:bg-purple-700 transition-colors shadow-md shadow-purple-200"
            >
              Start Camera
            </button>
            <button
              onClick={stopCamera}
              className="px-5 py-2.5 rounded-xl bg-white border border-purple-200 text-gray-600 text-sm font-bold hover:bg-purple-50 transition-colors"
            >
              Stop
            </button>
            <button
              onClick={switchCamera}
              className="px-5 py-2.5 rounded-xl bg-white border border-purple-200 text-gray-600 text-sm font-bold hover:bg-purple-50 transition-colors flex items-center gap-2"
            >
              <CameraIcon className="w-4 h-4" />
              Switch
            </button>
          </div>

          {/* Video viewport */}
          <div className="relative rounded-2xl overflow-hidden bg-purple-600 aspect-video border border-black shadow-lg">
            <video ref={videoRef} playsInline autoPlay muted className="w-full h-full object-cover"></video>
            <canvas ref={canvasRef} className="hidden"></canvas>

            {/* Scan line */}
            {running && (
              <div className="scan-line pointer-events-none absolute left-4 right-4 h-0.5 rounded-full bg-gradient-to-r from-transparent via-purple-400 to-transparent"
                   style={{ animation: 'scanLine 2.4s ease-in-out infinite' }}></div>
            )}

            {/* Corner brackets */}
            {running && (
              <div className="corner-pulse pointer-events-none absolute inset-0">
                <div className="absolute top-4 left-4 w-8 h-8 border-t-2 border-l-2 border-purple-400 rounded-tl-lg"></div>
                <div className="absolute top-4 right-4 w-8 h-8 border-t-2 border-r-2 border-purple-400 rounded-tr-lg"></div>
                <div className="absolute bottom-4 left-4 w-8 h-8 border-b-2 border-l-2 border-purple-400 rounded-bl-lg"></div>
                <div className="absolute bottom-4 right-4 w-8 h-8 border-b-2 border-r-2 border-purple-400 rounded-br-lg"></div>
              </div>
            )}

            {/* Idle overlay */}
            {!running && (
              <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 text-white">
                <Camera className="w-14 h-14 text-black" />
                <p className="text-sm font-extrabold tracking-wide text-black">Press "Start Camera" to begin</p>
              </div>
            )}
          </div>
        </div>

        {/* Manual entry + image upload */}
        <div className="space-y-4">
          {/* Manual entry */}
          <div className="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-6 shadow-sm">
            <p className="text-sm font-bold text-black mb-3">Enter reference manually</p>
            <div className="flex gap-2">
              <input
                type="text"
                value={codeInput}
                onChange={(e) => setCodeInput(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleManualVerify()}
                placeholder="PA_..."
                className="flex-1 rounded-xl border border-purple-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400"
              />
              <button
                onClick={handleManualVerify}
                className="px-5 py-2.5 rounded-xl bg-purple-600 text-black border border-black text-sm font-bold hover:bg-purple-700 transition-colors shadow-md shadow-purple-200"
              >
                Verify
              </button>
            </div>
          </div>

          {/* Image upload */}
          <div className="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-6 shadow-sm">
            <p className="text-sm font-bold text-black mb-3">Or upload a QR screenshot</p>
            <input
              type="file"
              accept="image/*"
              onChange={(e) => handleFileUpload(e.target.files[0])}
              className="hidden"
              id="file-input"
            />
            <label
              htmlFor="file-input"
              className="rounded-2xl border-2 border-dashed border-purple-200 bg-white hover:border-purple-400 transition-colors p-8 flex flex-col items-center justify-center cursor-pointer gap-3"
            >
              <div className="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                <Upload className="w-6 h-6 text-purple-500" />
              </div>
              <p className="text-sm font-semibold text-gray-900">Drop image here or <span className="text-purple-600 underline">click to browse</span></p>
            </label>
          </div>

          {/* Inline status */}
          {inlineResult && (
            <div className="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-5 shadow-sm">
              <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-2 ${
                inlineResult.kind === 'ok' ? 'bg-emerald-100 text-emerald-700' :
                inlineResult.kind === 'warn' ? 'bg-amber-100 text-amber-700' :
                'bg-red-100 text-red-700'
              }`}>
                {inlineResult.kind === 'ok' ? '✓ Valid' : inlineResult.kind === 'warn' ? '⚠ Already Used' : '✕ Invalid'}
              </span>
              <p className="text-sm text-black font-medium">{inlineResult.msg}</p>
            </div>
          )}

          {/* Debug Info */}
          {(debugInfo.qrData || debugInfo.apiResponse) && (
            <div className="bg-gray-900 border border-gray-700 rounded-3xl p-4 shadow-sm">
              <p className="text-xs font-bold text-gray-400 mb-2">DEBUG INFO</p>
              {debugInfo.qrData && (
                <div className="mb-3">
                  <p className="text-xs font-semibold text-green-400 mb-1">QR Code Detected:</p>
                  <p className="text-xs text-gray-300 break-all font-mono">{debugInfo.qrData}</p>
                </div>
              )}
              {debugInfo.apiResponse && (
                <div>
                  <p className="text-xs font-semibold text-blue-400 mb-1">API Response:</p>
                  <pre className="text-xs text-gray-300 break-all font-mono">{JSON.stringify(debugInfo.apiResponse, null, 2)}</pre>
                </div>
              )}
            </div>
          )}
        </div>
      </div>

      {/* Scan Result Modal */}
      {modal && (
        <div className="fixed inset-0 z-[70] flex items-center justify-center p-4" style={{ background: 'rgba(0,0,0,.82)', backdropFilter: 'blur(20px) saturate(1.8)' }}>
          <div className="relative w-full max-w-md rounded-3xl bg-zinc-950 border border-white/10 p-8 text-center shadow-2xl overflow-hidden">
            {/* Icon */}
            <div className="relative mb-6 flex items-center justify-center">
              <div className="relative flex items-center justify-center w-28 h-28">
                <div className={`absolute inset-0 rounded-full ring-pulse ${
                  modal.kind === 'ok' ? 'bg-gradient-to-r from-purple-600 to-purple-900' :
                  modal.kind === 'warn' ? 'bg-gradient-to-r from-amber-500 to-amber-700' :
                  'bg-gradient-to-r from-red-500 to-red-700'
                }`}></div>
                <div className="absolute inset-2 rounded-full bg-zinc-950"></div>
                {modal.kind === 'ok' && <CheckCircle className="relative w-14 h-14 text-purple-400" />}
                {modal.kind === 'warn' && <AlertTriangle className="relative w-14 h-14 text-amber-400" />}
                {modal.kind === 'err' && <XCircle className="relative w-14 h-14 text-red-400" />}
              </div>
            </div>

            <h3 className="text-2xl font-black text-white mb-1">{modal.title}</h3>

            {/* Buyer info */}
            {modal.buyer && (
              <div className="mb-3">
                <div className="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-2xl px-4 py-3 mt-1">
                  <div className="w-4 h-4 bg-purple-400 rounded-full shrink-0"></div>
                  <div className="text-left">
                    <p className="text-sm font-bold text-white leading-tight">{modal.buyer.name}</p>
                    <p className="text-xs text-purple-300 font-medium">{modal.buyer.email}</p>
                  </div>
                </div>
              </div>
            )}

            <p className="text-zinc-400 text-sm font-medium mb-1">{modal.sub}</p>
            <p className="text-zinc-600 text-xs mb-6">{modal.last ? 'Last check-in: ' + new Date(modal.last).toLocaleString() : ''}</p>

            <button
              onClick={closeModal}
              className="mt-1 px-8 py-3 rounded-xl bg-white text-zinc-900 font-black text-sm hover:bg-purple-50 transition-colors w-full"
            >
              Close
            </button>
          </div>
        </div>
      )}

      <style>{`
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scanLine { 0% { top: 8%; opacity: 0.8; } 50% { top: 88%; opacity: 1; } 100% { top: 8%; opacity: 0.8; } }
        @keyframes cornerPulse { 0%, 100% { opacity: 0.6; } 50% { opacity: 1; } }
        @keyframes ringPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(139,92,246,.45); } 50% { box-shadow: 0 0 0 22px rgba(139,92,246,0); } }
        .scan-line { animation: scanLine 2.4s ease-in-out infinite; }
        .corner-pulse { animation: cornerPulse 1.8s ease-in-out infinite; }
        .ring-pulse { animation: ringPulse 1.4s ease-in-out infinite; }
      `}</style>
    </div>
  );
}

export default Scanner;
