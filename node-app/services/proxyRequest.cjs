const http = require('http');
const https = require('https');
const { resolveLaravelOrigin } = require('../config/runtime.cjs');

function sanitizeRequestHeaders(headers, targetUrl, req) {
  const nextHeaders = { ...headers };

  delete nextHeaders.connection;
  delete nextHeaders['content-length'];
  nextHeaders.host = targetUrl.host;

  if (!nextHeaders['x-forwarded-host'] && headers.host) {
    nextHeaders['x-forwarded-host'] = headers.host;
  }
  if (!nextHeaders['x-forwarded-proto']) {
    nextHeaders['x-forwarded-proto'] = req.protocol || 'https';
  }
  if (!nextHeaders['x-forwarded-for'] && req.ip) {
    nextHeaders['x-forwarded-for'] = req.ip;
  }

  return nextHeaders;
}

function writeProxyError(res, err) {
  if (!res.headersSent) {
    res.statusCode = 502;
    res.setHeader('content-type', 'application/json; charset=utf-8');
  }

  res.end(
    JSON.stringify({
      ok: false,
      error: 'proxy_error',
      message: err.message,
    })
  );
}

function proxyToLaravel(req, res) {
  const origin = resolveLaravelOrigin();
  const targetUrl = new URL(req.originalUrl || req.url, origin);
  const isHttps = targetUrl.protocol === 'https:';
  const transport = isHttps ? https : http;

  const headers = sanitizeRequestHeaders(req.headers || {}, targetUrl, req);

  const options = {
    protocol: targetUrl.protocol,
    hostname: targetUrl.hostname,
    port: targetUrl.port || (isHttps ? 443 : 80),
    method: req.method,
    path: `${targetUrl.pathname}${targetUrl.search}`,
    headers,
  };

  const proxyReq = transport.request(options, (proxyRes) => {
    res.statusCode = proxyRes.statusCode || 502;

    for (const [key, value] of Object.entries(proxyRes.headers || {})) {
      if (typeof value === 'undefined') continue;
      res.setHeader(key, value);
    }

    proxyRes.pipe(res);
  });

  proxyReq.on('error', (err) => {
    writeProxyError(res, err);
  });

  proxyReq.setTimeout(30_000, () => {
    proxyReq.destroy(new Error('Upstream timeout'));
  });

  if (req.readable && req.method !== 'GET' && req.method !== 'HEAD') {
    req.pipe(proxyReq);
  } else {
    proxyReq.end();
  }
}

module.exports = {
  proxyToLaravel,
  proxyRequest: proxyToLaravel,
  writeProxyError,
};
