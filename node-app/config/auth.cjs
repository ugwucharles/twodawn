const DEFAULT_SESSION_COOKIE_NAME = 'twodawn_node_session';
const DEFAULT_SESSION_TTL_SECONDS = 60 * 60 * 24 * 7; // 7 days
const DEFAULT_SESSION_SAME_SITE = 'lax';
const DEFAULT_PASSWORD_RESET_TTL_MINUTES = 60;
const DEFAULT_EMAIL_VERIFICATION_TTL_MINUTES = 60;
const DEFAULT_BCRYPT_ROUNDS = 12;

function firstNonEmpty(...values) {
  for (const value of values) {
    if (value === undefined || value === null) continue;
    const text = String(value).trim();
    if (text.length > 0) return text;
  }
  return null;
}

function asPositiveInteger(value, fallback) {
  const parsed = Number(value);
  if (!Number.isFinite(parsed) || parsed <= 0) return fallback;
  return Math.floor(parsed);
}

function asBoolean(value, fallback = false) {
  if (value === undefined || value === null || String(value).trim() === '') return fallback;

  const raw = String(value).trim().toLowerCase();
  if (['1', 'true', 'yes', 'on'].includes(raw)) return true;
  if (['0', 'false', 'no', 'off'].includes(raw)) return false;

  return fallback;
}

function normalizeSameSite(value) {
  const raw = String(value || '').trim().toLowerCase();
  if (raw === 'strict') return 'Strict';
  if (raw === 'none') return 'None';
  return 'Lax';
}

function readAuthConfig(env = process.env) {
  const sessionSecret = firstNonEmpty(env.NODE_SESSION_SECRET, env.APP_KEY);

  return {
    sessionSecret,
    sessionCookieName: firstNonEmpty(env.NODE_SESSION_COOKIE_NAME, DEFAULT_SESSION_COOKIE_NAME),
    sessionTtlSeconds: asPositiveInteger(env.NODE_SESSION_TTL_SECONDS, DEFAULT_SESSION_TTL_SECONDS),
    sessionCookieSameSite: normalizeSameSite(firstNonEmpty(env.NODE_SESSION_SAME_SITE, DEFAULT_SESSION_SAME_SITE)),
    sessionCookieSecure: asBoolean(env.NODE_SESSION_COOKIE_SECURE, false),
    passwordResetTtlMinutes: asPositiveInteger(
      env.NODE_PASSWORD_RESET_TTL_MINUTES,
      DEFAULT_PASSWORD_RESET_TTL_MINUTES
    ),
    emailVerificationTtlMinutes: asPositiveInteger(
      env.NODE_EMAIL_VERIFICATION_TTL_MINUTES,
      DEFAULT_EMAIL_VERIFICATION_TTL_MINUTES
    ),
    bcryptRounds: asPositiveInteger(env.NODE_BCRYPT_ROUNDS, DEFAULT_BCRYPT_ROUNDS),
    exposeResetToken: asBoolean(env.NODE_EXPOSE_RESET_TOKEN, false),
    exposeVerificationLink: asBoolean(env.NODE_EXPOSE_VERIFICATION_LINK, false),
    publicAuthEnabled: asBoolean(env.NODE_AUTH_PUBLIC_ENABLED, true),
    proxyAuthGetPages: asBoolean(env.NODE_AUTH_PROXY_GET, true),
  };
}

function isSessionConfigured(config) {
  return Boolean(config.sessionSecret && String(config.sessionSecret).trim().length > 0);
}

function toPublicAuthConfig(config) {
  return {
    sessionConfigured: isSessionConfigured(config),
    sessionCookieName: config.sessionCookieName,
    sessionTtlSeconds: config.sessionTtlSeconds,
    sessionCookieSameSite: config.sessionCookieSameSite,
    sessionCookieSecure: config.sessionCookieSecure,
    passwordResetTtlMinutes: config.passwordResetTtlMinutes,
    emailVerificationTtlMinutes: config.emailVerificationTtlMinutes,
    bcryptRounds: config.bcryptRounds,
    publicAuthEnabled: config.publicAuthEnabled,
    proxyAuthGetPages: config.proxyAuthGetPages,
  };
}

module.exports = {
  readAuthConfig,
  isSessionConfigured,
  toPublicAuthConfig,
};
