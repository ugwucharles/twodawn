const crypto = require('crypto');
const { readAuthConfig, isSessionConfigured } = require('../config/auth.cjs');
const { parseCookies, setCookie, clearCookie } = require('../lib/httpCookies.cjs');
const { signPayload, verifySignedPayload } = require('../lib/signedToken.cjs');
const { findAuthUserById, toSessionUser } = require('../models/authUserModel.cjs');

function nowInSeconds() {
  return Math.floor(Date.now() / 1000);
}

function resolveCookieOptions(req, config, overrides = {}) {
  return {
    path: '/',
    httpOnly: true,
    secure: config.sessionCookieSecure || Boolean(req.secure),
    sameSite: config.sessionCookieSameSite,
    ...overrides,
  };
}

function buildSessionClaims(user, ttlSeconds, remember = false, options = {}) {
  const issuedAt = nowInSeconds();
  const claims = {
    sub: user.id,
    iat: issuedAt,
    exp: issuedAt + ttlSeconds,
    remember: Boolean(remember),
    jti: crypto.randomBytes(16).toString('hex'),
  };

  if (options.passwordConfirmedAt) {
    claims.pwd_confirmed_at = Number(options.passwordConfirmedAt);
  } else if (options.preservePasswordConfirmation && options.existingClaims?.pwd_confirmed_at) {
    claims.pwd_confirmed_at = Number(options.existingClaims.pwd_confirmed_at);
  }

  return claims;
}

async function attachSessionUser(req, res, next) {
  const config = readAuthConfig();
  req.auth = {
    configured: isSessionConfigured(config),
    isAuthenticated: false,
    user: null,
    claims: null,
  };

  if (!req.auth.configured) return next();

  const cookies = parseCookies(req.headers.cookie);
  let rawToken = cookies[config.sessionCookieName];

  if (!rawToken && req.headers.authorization && req.headers.authorization.startsWith('Bearer ')) {
    rawToken = req.headers.authorization.split(' ')[1];
  }

  if (!rawToken) return next();

  const claims = verifySignedPayload(rawToken, config.sessionSecret);
  if (!claims || Number(claims.exp || 0) <= nowInSeconds()) {
    clearCookie(res, config.sessionCookieName, resolveCookieOptions(req, config));
    return next();
  }

  try {
    const user = await findAuthUserById(claims.sub);
    if (!user) {
      clearCookie(res, config.sessionCookieName, resolveCookieOptions(req, config));
      return next();
    }

    req.auth.isAuthenticated = true;
    req.auth.user = toSessionUser(user);
    req.auth.claims = claims;
    return next();
  } catch (error) {
    return next(error);
  }
}

function startSession(res, req, user, options = {}) {
  const config = readAuthConfig();
  if (!isSessionConfigured(config)) {
    throw new Error('Session secret is not configured. Set NODE_SESSION_SECRET (or APP_KEY).');
  }

  const remember = Boolean(options.remember);
  const safeUser = toSessionUser(user);
  const claims = buildSessionClaims(safeUser, config.sessionTtlSeconds, remember, {
    passwordConfirmedAt: options.passwordConfirmedAt,
    preservePasswordConfirmation: options.preservePasswordConfirmation !== false,
    existingClaims: options.existingClaims || req.auth?.claims || null,
  });
  const token = signPayload(claims, config.sessionSecret);

  setCookie(
    res,
    config.sessionCookieName,
    token,
    resolveCookieOptions(req, config, {
      maxAge: config.sessionTtlSeconds,
    })
  );

  return { user: safeUser, token };
}

function clearSession(res, req) {
  const config = readAuthConfig();
  clearCookie(res, config.sessionCookieName, resolveCookieOptions(req, config));
}

function requireAuthenticated(req, res, next) {
  if (req.auth && req.auth.isAuthenticated) {
    return next();
  }

  return res.status(401).json({
    ok: false,
    error: 'unauthenticated',
    message: 'Authentication required.',
  });
}

function requireAdmin(req, res, next) {
  if (!req.auth || !req.auth.isAuthenticated) {
    return res.status(401).json({
      ok: false,
      error: 'unauthenticated',
      message: 'Authentication required.',
    });
  }

  if (!req.auth.user || !req.auth.user.is_admin) {
    return res.status(403).json({
      ok: false,
      error: 'forbidden',
      message: 'Admin access required.',
    });
  }

  return next();
}

module.exports = {
  attachSessionUser,
  startSession,
  clearSession,
  requireAuthenticated,
  requireAdmin,
};
