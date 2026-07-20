const logger = require('../lib/logger.cjs');

function errorHandler(err, req, res, next) {
  logger.error('Unhandled error', err, {
    path: req.path,
    method: req.method,
    ip: req.ip,
    userAgent: req.headers['user-agent']
  });

  // Default error
  const error = {
    ok: false,
    error: 'Internal server error',
    message: process.env.NODE_ENV === 'production' ? 'An error occurred' : err.message
  };

  // Handle specific error types
  if (err.name === 'ValidationError') {
    error.error = 'validation';
    error.message = err.message;
    return res.status(400).json(error);
  }

  if (err.name === 'UnauthorizedError') {
    error.error = 'unauthorized';
    error.message = 'Invalid or expired token';
    return res.status(401).json(error);
  }

  if (err.name === 'NotFoundError') {
    error.error = 'not_found';
    error.message = err.message || 'Resource not found';
    return res.status(404).json(error);
  }

  // Default to 500
  res.status(500).json(error);
}

function notFoundHandler(req, res) {
  logger.warn('Route not found', { path: req.path, method: req.method });
  
  res.status(404).json({
    ok: false,
    error: 'not_found',
    message: 'Route not found'
  });
}

module.exports = { errorHandler, notFoundHandler };
