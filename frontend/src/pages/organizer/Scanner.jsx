import { useState, useRef, useEffect, useCallback } from 'react';
import { Camera, Upload, CheckCircle, AlertTriangle, XCircle, Camera as CameraIcon } from 'lucide-react';
import api from '../../services/api';
import jsQR from 'jsqr';

// Delay before the result popup appears after a scan (ms)
const POPUP_DELAY_MS = 3000;

function Scanner() {
  const [stream, setStream] = useState(null);
  const [running, setRunning] = useState(false);
  const [status, setStatus] = useState({ msg: '', kind: 'idle' });
  const [inlineResult, setInlineResult] = useState(null);
  const [modal, setModal] = useState(null);
  const [codeInput, setCodeInput] = useState('');
  const [facingMode, setFacingMode] = useState('environment');

  // Countdown state for the 3-second hold
  const [countdown, setCountdown] = useState(null); // null | number

  const videoRef = useRef(null);
  const canvasRef = useRef(null);
  const rafRef = useRef(null);
  const runningRef = useRef(false);
  const streamRef = useRef(null);
  const facingModeRef = useRef('environment');
  const countdownIntervalRef = useRef(null);

  useEffect(() => {
    facingModeRef.current = facingMode;
  }, [facingMode]);

  useEffect(() => {
    return () => {
      stopCamera();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const setStatusMsg = (msg, kind = 'idle') => setStatus({ msg, kind });

  const showInlineResult = (kind, msg) => setInlineResult({ kind, msg });

  const openModal = (kind, opts = {}) => setModal({ kind, ...opts });

  const closeModal = () => setModal(null);

  // Formats a date/time nicely for "scanned at" display
  const formatScanTime = (ts) => {
    if (!ts) return '';
    try {
      return new Date(ts).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
      });
    } catch {
      return ts;
    }
  };

  // Run a 3-second countdown then call the callback
  const withCountdown = (callback) => {
    let remaining = 3;
    setCountdown(remaining);
    countdownIntervalRef.current = setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) {
        clearInterval(countdownIntervalRef.current);
        countdownIntervalRef.current = null;
        setCountdown(null);
        callback();
      } else {
        setCountdown(remaining);
      }
    }, 1000);
  };

  const verifyText = useCallback(async (text) => {
    const trimmed = (text || '').trim();
    if (!trimmed) {
      showInlineResult('err', 'Please enter a ticket code.');
      return;
    }
    setStatusMsg('Verifying…', 'scanning');
    try {
      console.log('Sending verification request for:', trimmed);
      const res = await api.post('/organizer/scanner/verify', { code: trimmed });
      const data = res.data;
      console.log('Verification response:', data);

      if (data.valid) {
        setStatusMsg('Approved ✓', 'ok');
        showInlineResult('ok', `${data.buyer?.name || 'Guest'} checked in to "${data.event?.title || 'event'}"`);
        openModal('ok', {
          title: '✓ Check-in Approved!',
          sub: data.event?.title || '',
          buyer: data.buyer,
          last: data.last_checkin_at,
        });
      } else if (data.already) {
        const scannedAt = formatScanTime(data.last_checkin_at);
        setStatusMsg('Already Scanned', 'warn');
        showInlineResult('warn', `Ticket scanned already${scannedAt ? ' at ' + scannedAt : ''}. Buyer: ${data.buyer?.name || 'Unknown'}`);
        openModal('warn', {
          title: 'Ticket Scanned Already',
          sub: data.event?.title || '',
          buyer: data.buyer,
          last: data.last_checkin_at,
        });
      } else {
        setStatusMsg('Invalid Ticket', 'err');
        const msg = data.message || 'This ticket is not valid.';
        console.log('Invalid ticket reason:', msg);
        showInlineResult('err', msg);
        openModal('err', { title: 'Ticket Not Valid', sub: msg });
      }
    } catch (e) {
      console.error('Verification error:', e);
      const errMsg = e.response?.data?.message || e.message || 'Network error. Please try again.';
      setStatusMsg('Error', 'err');
      showInlineResult('err', errMsg);
      openModal('err', { title: 'Connection Error', sub: errMsg });
    }
  }, []);

  // The scan loop — uses runningRef so it never goes stale
  const tick = useCallback(() => {
    if (!runningRef.current) return;
    try {
      if (videoRef.current && canvasRef.current) {
        const video = videoRef.current;
        if (video.readyState >= 2 && video.videoWidth > 0 && video.videoHeight > 0) {
          const canvas = canvasRef.current;
          const ctx = canvas.getContext('2d');
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          ctx.drawImage(video, 0, 0);
          const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'dontInvert',
          });
          if (code && code.data) {
            console.log('QR code detected:', code.data);
            // Wait 3 seconds before showing the popup
            setStatusMsg('QR detected — verifying…', 'scanning');
            withCountdown(() => {
              stopCamera();
              verifyText(code.data);
            });
            return;
          }
        }
      }
    } catch (_) { /* ignore canvas errors */ }
    rafRef.current = requestAnimationFrame(tick);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [verifyText]);

  const startCamera = async () => {
    try {
      setStatusMsg('Starting camera…', 'scanning');
      const s = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: facingModeRef.current },
        audio: false,
      });
      if (!s) throw new Error('No camera found');
      streamRef.current = s;
      setStream(s);
      if (videoRef.current) {
        videoRef.current.srcObject = s;
        await videoRef.current.play();
      }
      runningRef.current = true;
      setRunning(true);
      setStatusMsg('Scanning…', 'scanning');
      rafRef.current = requestAnimationFrame(tick);
    } catch (e) {
      console.error('Camera start error:', e);
      setStatusMsg('Camera error: ' + (e?.message || e), 'err');
      showInlineResult('err', 'Allow camera access, or use the image upload / manual entry.');
    }
  };

  const stopCamera = () => {
    runningRef.current = false;
    setRunning(false);
    if (rafRef.current) {
      cancelAnimationFrame(rafRef.current);
      rafRef.current = null;
    }
    if (videoRef.current) {
      videoRef.current.pause?.();
      videoRef.current.srcObject = null;
    }
    const s = streamRef.current;
    if (s) {
      s.getTracks().forEach(t => t.stop());
      streamRef.current = null;
      setStream(null);
    }
    // Also cancel any pending countdown
    if (countdownIntervalRef.current) {
      clearInterval(countdownIntervalRef.current);
      countdownIntervalRef.current = null;
      setCountdown(null);
    }
    setStatusMsg('Ready');
  };

  const switchCamera = async () => {
    const wasRunning = runningRef.current;
    stopCamera();
    const next = facingModeRef.current === 'environment' ? 'user' : 'environment';
    facingModeRef.current = next;
    setFacingMode(next);
    if (wasRunning) {
      setTimeout(() => startCamera(), 150);
    }
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
      const reader = new FileReader();
      reader.onload = async (e) => {
        const img = new Image();
        img.onload = () => {
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          canvas.width = img.width;
          canvas.height = img.height;
          ctx.drawImage(img, 0, 0);
          const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'dontInvert',
          });
          if (code && code.data) {
            console.log('QR code detected from image:', code.data);
            setStatusMsg('QR found — verifying…', 'scanning');
            withCountdown(() => verifyText(code.data));
          } else {
            setStatusMsg('Error', 'err');
            showInlineResult('err', 'No QR code found in image. Try a clearer photo.');
          }
        };
        img.onerror = () => {
          setStatusMsg('Error', 'err');
          showInlineResult('err', 'Failed to load image.');
        };
        img.src = e.target.result;
      };
      reader.onerror = () => {
        setStatusMsg('Error', 'err');
        showInlineResult('err', 'Failed to read file.');
      };
      reader.readAsDataURL(file);
    } catch (e) {
      console.error('Image upload error:', e);
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
              disabled={running || countdown !== null}
              className="px-5 py-2.5 rounded-xl bg-purple-600 text-black border border-black text-sm font-bold hover:bg-purple-700 transition-colors shadow-md shadow-purple-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Start Camera
            </button>
            <button
              onClick={stopCamera}
              disabled={!running && countdown === null}
              className="px-5 py-2.5 rounded-xl bg-white border border-purple-200 text-gray-600 text-sm font-bold hover:bg-purple-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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

            {/* Idle overlay */}
            {!running && (
              <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 text-white">
                <Camera className="w-14 h-14 text-black" />
                <p className="text-sm font-extrabold tracking-wide text-black">Press &quot;Start Camera&quot; to begin</p>
              </div>
            )}
          </div>
        </div>

        {/* Manual entry + image upload */}
        <div className="space-y-4">
          {/* Inline status */}
          {inlineResult && (
            <div className="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-5 shadow-sm">
              <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mb-2 ${
                inlineResult.kind === 'ok' ? 'bg-emerald-100 text-emerald-700' :
                inlineResult.kind === 'warn' ? 'bg-amber-100 text-amber-700' :
                'bg-red-100 text-red-700'
              }`}>
                {inlineResult.kind === 'ok' ? '✓ Checked In' : inlineResult.kind === 'warn' ? '⚠ Already Scanned' : '✕ Invalid'}
              </span>
              <p className="text-sm text-black font-medium">{inlineResult.msg}</p>
            </div>
          )}

          {/* Manual entry */}
          <div className="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-3xl p-6 shadow-sm">
            <p className="text-sm font-bold text-black mb-3">Enter reference manually</p>
            <div className="flex gap-2">
              <input
                type="text"
                value={codeInput}
                onChange={(e) => setCodeInput(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleManualVerify()}
                placeholder="Ticket code or PA_..."
                className="flex-1 rounded-xl border border-purple-200 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-400"
              />
              <button
                onClick={handleManualVerify}
                disabled={countdown !== null}
                className="px-5 py-2.5 rounded-xl bg-purple-600 text-black border border-black text-sm font-bold hover:bg-purple-700 transition-colors shadow-md shadow-purple-200 disabled:opacity-50 disabled:cursor-not-allowed"
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
            {modal.kind === 'warn' && modal.last && (
              <p className="text-amber-400/80 text-xs font-semibold mb-6">
                Previously scanned at {formatScanTime(modal.last)}
              </p>
            )}
            {modal.kind !== 'warn' && (
              <p className="text-zinc-600 text-xs mb-6">
                {modal.last && modal.kind === 'ok' ? 'Checked in: ' + formatScanTime(modal.last) : ''}
              </p>
            )}

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
        @keyframes countdownPop { 0% { transform: scale(1.4); opacity: 0; } 20% { transform: scale(1); opacity: 1; } 80% { transform: scale(1); opacity: 1; } 100% { transform: scale(0.9); opacity: 0.6; } }
        .scan-line { animation: scanLine 2.4s ease-in-out infinite; }
        .corner-pulse { animation: cornerPulse 1.8s ease-in-out infinite; }
        .ring-pulse { animation: ringPulse 1.4s ease-in-out infinite; }
        .countdown-num { animation: countdownPop 1s ease-in-out forwards; }
      `}</style>
    </div>
  );
}

export default Scanner;
