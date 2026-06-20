const crypto = require('crypto');
const bcrypt = require('bcryptjs');
const { readAuthConfig } = require('../config/auth.cjs');
const { findAuthUserByEmail, setPasswordForUser } = require('../models/authUserModel.cjs');
const {
  upsertPasswordResetToken,
  findPasswordResetToken,
  deletePasswordResetToken,
} = require('../models/passwordResetModel.cjs');

function normalizeEmail(value) {
  return String(value || '').trim().toLowerCase();
}

function randomRememberToken() {
  return crypto.randomBytes(40).toString('hex').slice(0, 60);
}

function isResetTokenExpired(createdAt, ttlMinutes) {
  const createdAtMs = new Date(createdAt).getTime();
  if (!Number.isFinite(createdAtMs)) return true;

  const ageMs = Date.now() - createdAtMs;
  return ageMs > ttlMinutes * 60 * 1000;
}

async function issuePasswordResetToken(email) {
  const normalizedEmail = normalizeEmail(email);
  if (!normalizedEmail) {
    return { ok: false, reason: 'email_required' };
  }

  const user = await findAuthUserByEmail(normalizedEmail);
  if (!user) {
    // Return success semantics to avoid account enumeration.
    return { ok: true, issued: false, email: normalizedEmail };
  }

  const config = readAuthConfig();
  const token = crypto.randomBytes(32).toString('hex');
  const tokenHash = await bcrypt.hash(token, config.bcryptRounds);
  await upsertPasswordResetToken(normalizedEmail, tokenHash);

  return {
    ok: true,
    issued: true,
    email: normalizedEmail,
    token,
  };
}

async function completePasswordReset({ email, token, password }) {
  const normalizedEmail = normalizeEmail(email);
  const normalizedToken = String(token || '').trim();
  const normalizedPassword = String(password || '');

  if (!normalizedEmail || !normalizedToken || !normalizedPassword) {
    return { ok: false, reason: 'invalid_payload' };
  }

  const config = readAuthConfig();
  const stored = await findPasswordResetToken(normalizedEmail);
  if (!stored) {
    return { ok: false, reason: 'invalid_token' };
  }

  if (isResetTokenExpired(stored.created_at, config.passwordResetTtlMinutes)) {
    await deletePasswordResetToken(normalizedEmail);
    return { ok: false, reason: 'expired_token' };
  }

  const tokenMatches = await bcrypt.compare(normalizedToken, String(stored.token || ''));
  if (!tokenMatches) {
    return { ok: false, reason: 'invalid_token' };
  }

  const user = await findAuthUserByEmail(normalizedEmail);
  if (!user) {
    await deletePasswordResetToken(normalizedEmail);
    return { ok: false, reason: 'user_not_found' };
  }

  const passwordHash = await bcrypt.hash(normalizedPassword, config.bcryptRounds);
  await setPasswordForUser(user.id, passwordHash, randomRememberToken());
  await deletePasswordResetToken(normalizedEmail);

  return { ok: true, userId: user.id };
}

module.exports = {
  issuePasswordResetToken,
  completePasswordReset,
};
