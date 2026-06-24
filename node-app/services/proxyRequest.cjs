function writeProxyError(res, err) {
  if (!res.headersSent) {
    res.statusCode = 410;
    res.setHeader('content-type', 'application/json; charset=utf-8');
  }

  res.end(
    JSON.stringify({
      ok: false,
      error: 'laravel_removed',
      message: err?.message || 'This application no longer depends on Laravel.',
    })
  );
}

function proxyToLaravel(req, res) {
  writeProxyError(res, new Error('This application no longer uses the Laravel backend.'));
}

module.exports = {
  proxyToLaravel,
  proxyRequest: proxyToLaravel,
  writeProxyError,
};
