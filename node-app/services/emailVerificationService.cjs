const crypto = require('crypto');
const { readAuthConfig, isSessionConfigured } = require('../config/auth.cjs');

function hashEmailForVerification(email) {
  return crypto
    .createHash('sha1')
    .update(String(email || '').trim().toLowerCase())
    .digest('hex');
}

function signEmailVerification({ userId, hash, expires }, secret) {
  const payload = `${userId}|${hash}|${expires}`;
  return crypto.createHmac('sha256', secret).update(payload).digest('hex');
}

function safeEqual(left, right) {
  const leftBuffer = Buffer.from(String(left || ''));
  const rightBuffer = Buffer.from(String(right || ''));
  if (leftBuffer.length !== rightBuffer.length) return false;
  return crypto.timingSafeEqual(leftBuffer, rightBuffer);
}

function createEmailVerificationLink({ user, origin, pathPrefix = '/_node/auth/email/verify' }) {
  const config = readAuthConfig();
  if (!isSessionConfigured(config)) {
    throw new Error('Session secret is not configured. Set NODE_SESSION_SECRET (or APP_KEY).');
  }

  const safeOrigin = String(origin || '').trim().replace(/\/+$/g, '');
  const expires = Math.floor(Date.now() / 1000) + config.emailVerificationTtlMinutes * 60;
  const hash = hashEmailForVerification(user.email);
  const signature = signEmailVerification({ userId: user.id, hash, expires }, config.sessionSecret);
  const normalizedPrefix = String(pathPrefix || '/_node/auth/email/verify').replace(/\/+$/g, '');
  const path = `${normalizedPrefix}/${encodeURIComponent(user.id)}/${hash}`;
  const query = new URLSearchParams({
    expires: String(expires),
    signature,
  });

  return `${safeOrigin}${path}?${query.toString()}`;
}

function verifyEmailVerificationRequest({ user, routeUserId, routeHash, expires, signature }) {
  const config = readAuthConfig();
  if (!isSessionConfigured(config)) {
    return { ok: false, reason: 'session_not_configured' };
  }

  const expectedUserId = String(user?.id || '');
  const normalizedRouteUserId = String(routeUserId || '');
  if (!expectedUserId || expectedUserId !== normalizedRouteUserId) {
    return { ok: false, reason: 'user_mismatch' };
  }

  const expectedHash = hashEmailForVerification(user.email);
  const normalizedRouteHash = String(routeHash || '').trim().toLowerCase();
  if (!normalizedRouteHash || normalizedRouteHash !== expectedHash) {
    return { ok: false, reason: 'invalid_hash' };
  }

  const expiresAt = Number(expires);
  if (!Number.isFinite(expiresAt) || expiresAt <= 0) {
    return { ok: false, reason: 'invalid_expires' };
  }

  if (Math.floor(Date.now() / 1000) > expiresAt) {
    return { ok: false, reason: 'expired' };
  }

  const expectedSignature = signEmailVerification(
    { userId: expectedUserId, hash: expectedHash, expires: expiresAt },
    config.sessionSecret
  );

  if (!safeEqual(expectedSignature, signature)) {
    return { ok: false, reason: 'invalid_signature' };
  }

  return { ok: true };
}

module.exports = {
  hashEmailForVerification,
  createEmailVerificationLink,
  verifyEmailVerificationRequest,
};
