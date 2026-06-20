function parseCookies(cookieHeader) {
  const parsed = {};
  const source = String(cookieHeader || '').trim();
  if (!source) return parsed;

  for (const segment of source.split(';')) {
    const part = segment.trim();
    if (!part) continue;

    const separatorIndex = part.indexOf('=');
    if (separatorIndex <= 0) continue;

    const name = part.slice(0, separatorIndex).trim();
    const value = part.slice(separatorIndex + 1).trim();
    if (!name) continue;

    try {
      parsed[name] = decodeURIComponent(value);
    } catch (_error) {
      parsed[name] = value;
    }
  }

  return parsed;
}

function serializeCookie(name, value, options = {}) {
  const encodedName = String(name || '').trim();
  if (!encodedName) {
    throw new Error('Cookie name is required');
  }

  const encodedValue = encodeURIComponent(String(value ?? ''));
  const segments = [`${encodedName}=${encodedValue}`];

  const path = String(options.path || '/').trim() || '/';
  segments.push(`Path=${path}`);

  if (options.maxAge !== undefined && options.maxAge !== null) {
    const maxAge = Math.max(0, Math.floor(Number(options.maxAge) || 0));
    segments.push(`Max-Age=${maxAge}`);
  }

  if (options.expires instanceof Date) {
    segments.push(`Expires=${options.expires.toUTCString()}`);
  }

  if (options.httpOnly !== false) {
    segments.push('HttpOnly');
  }

  if (options.secure) {
    segments.push('Secure');
  }

  if (options.sameSite) {
    segments.push(`SameSite=${options.sameSite}`);
  }

  return segments.join('; ');
}

function appendSetCookieHeader(res, cookieValue) {
  const existing = res.getHeader('set-cookie');

  if (!existing) {
    res.setHeader('set-cookie', [cookieValue]);
    return;
  }

  if (Array.isArray(existing)) {
    res.setHeader('set-cookie', [...existing, cookieValue]);
    return;
  }

  res.setHeader('set-cookie', [String(existing), cookieValue]);
}

function setCookie(res, name, value, options = {}) {
  appendSetCookieHeader(res, serializeCookie(name, value, options));
}

function clearCookie(res, name, options = {}) {
  setCookie(res, name, '', {
    ...options,
    maxAge: 0,
    expires: new Date(0),
  });
}

module.exports = {
  parseCookies,
  serializeCookie,
  setCookie,
  clearCookie,
};
