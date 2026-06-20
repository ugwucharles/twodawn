const crypto = require('crypto');

function toBase64Url(buffer) {
  return Buffer.from(buffer)
    .toString('base64')
    .replace(/\+/g, '-')
    .replace(/\//g, '_')
    .replace(/=+$/g, '');
}

function fromBase64Url(text) {
  const normalized = String(text || '')
    .replace(/-/g, '+')
    .replace(/_/g, '/');

  const padded = normalized + '='.repeat((4 - (normalized.length % 4 || 4)) % 4);
  return Buffer.from(padded, 'base64');
}

function createSignature(payloadSegment, secret) {
  const digest = crypto.createHmac('sha256', secret).update(String(payloadSegment || '')).digest();
  return toBase64Url(digest);
}

function safeEqual(left, right) {
  const leftBuffer = Buffer.from(String(left || ''));
  const rightBuffer = Buffer.from(String(right || ''));
  if (leftBuffer.length !== rightBuffer.length) return false;
  return crypto.timingSafeEqual(leftBuffer, rightBuffer);
}

function signPayload(payload, secret) {
  const payloadJson = JSON.stringify(payload || {});
  const payloadSegment = toBase64Url(Buffer.from(payloadJson, 'utf8'));
  const signature = createSignature(payloadSegment, secret);
  return `v1.${payloadSegment}.${signature}`;
}

function verifySignedPayload(token, secret) {
  const rawToken = String(token || '').trim();
  if (!rawToken) return null;

  const parts = rawToken.split('.');
  if (parts.length !== 3 || parts[0] !== 'v1') return null;

  const payloadSegment = parts[1];
  const signature = parts[2];
  const expectedSignature = createSignature(payloadSegment, secret);
  if (!safeEqual(signature, expectedSignature)) return null;

  try {
    const payloadBuffer = fromBase64Url(payloadSegment);
    return JSON.parse(payloadBuffer.toString('utf8'));
  } catch (_error) {
    return null;
  }
}

module.exports = {
  signPayload,
  verifySignedPayload,
};
