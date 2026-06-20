const { startSession, clearSession } = require('../services/sessionAuth.cjs');

function wantsJson(req) {
  const accept = String(req.headers.accept || '').toLowerCase();
  if (accept.includes('application/json')) return true;
  if (String(req.headers['x-requested-with'] || '').toLowerCase() === 'xmlhttprequest') return true;
  return false;
}

function safeReferer(req, fallback = '/') {
  const referer = String(req.get('referer') || '').trim();
  if (!referer) return fallback;

  try {
    const url = new URL(referer);
    return `${url.pathname}${url.search}`;
  } catch (_error) {
    return fallback;
  }
}

function appendQuery(path, params) {
  const url = new URL(path, 'http://local');
  for (const [key, value] of Object.entries(params)) {
    if (value === undefined || value === null || value === '') continue;
    url.searchParams.set(key, String(value));
  }
  return `${url.pathname}${url.search}`;
}

function sendAuthResult(req, res, result) {
  if (result.clearSession) {
    clearSession(res, req);
  }

  let token = undefined;
  if (result.session) {
    const sessionData = startSession(res, req, result.session.user, {
      remember: result.session.remember,
      passwordConfirmedAt: result.session.passwordConfirmedAt,
    });
    token = sessionData.token;
  }

  if (wantsJson(req)) {
    const responseBody = { ...result.body };
    if (token) {
      responseBody.token = token;
    }
    return res.status(result.status).json(responseBody);
  }

  if (result.ok && result.redirect) {
    return res.redirect(302, result.redirect);
  }

  if (!result.ok && result.errorRedirect) {
    return res.redirect(302, result.errorRedirect);
  }

  if (result.redirect) {
    return res.redirect(302, result.redirect);
  }

  return res.status(result.status).json(result.body);
}

function unauthenticatedResult(req, loginPath = '/organizer/login') {
  if (wantsJson(req)) {
    return {
      ok: false,
      status: 401,
      body: {
        ok: false,
        error: 'unauthenticated',
        message: 'Authentication required.',
      },
    };
  }

  return {
    ok: false,
    status: 302,
    errorRedirect: loginPath,
    body: { ok: false, error: 'unauthenticated' },
  };
}

function forbiddenResult(req, message = 'Forbidden.', fallback = '/') {
  if (wantsJson(req)) {
    return {
      ok: false,
      status: 403,
      body: {
        ok: false,
        error: 'forbidden',
        message,
      },
    };
  }

  return {
    ok: false,
    status: 302,
    errorRedirect: fallback,
    body: { ok: false, error: 'forbidden', message },
  };
}

module.exports = {
  wantsJson,
  safeReferer,
  appendQuery,
  sendAuthResult,
  unauthenticatedResult,
  forbiddenResult,
};
