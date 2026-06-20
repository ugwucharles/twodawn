const crypto = require('crypto');

function attachRequestContext(req, res, next) {
  const incomingRequestId = req.headers['x-request-id'];
  const requestId = incomingRequestId || crypto.randomUUID();

  req.requestId = requestId;
  req.headers['x-request-id'] = requestId;
  res.setHeader('x-request-id', requestId);

  next();
}

module.exports = {
  attachRequestContext,
};
