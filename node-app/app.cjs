require('./config/envLoader.cjs');
const express = require('express');
const { attachRequestContext } = require('./middleware/requestContext.cjs');
const { registerRoutes } = require('./routes/index.cjs');
const { createUpstreamProxy } = require('./services/upstreamProxy.cjs');

function createApp() {
  const app = express();

  app.disable('x-powered-by');
  app.set('trust proxy', true);

  app.use(attachRequestContext);

  app.use((req, res, next) => {
    const origin = req.headers.origin;
    if (origin) {
      res.setHeader('Access-Control-Allow-Origin', origin);
    } else {
      res.setHeader('Access-Control-Allow-Origin', '*');
    }
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    res.setHeader('Access-Control-Allow-Credentials', 'true');
    if (req.method === 'OPTIONS') {
      return res.sendStatus(200);
    }
    next();
  });

  registerRoutes(app);

  // During migration, everything not yet implemented in Node falls back to Laravel.
  // Disabled for now since we don't want to use Laravel
  // app.use(createUpstreamProxy());

  return app;
}

const app = createApp();

module.exports = app;
module.exports.createApp = createApp;
