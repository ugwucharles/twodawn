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
  registerRoutes(app);

  // During migration, everything not yet implemented in Node falls back to Laravel.
  // Disabled for now since we don't want to use Laravel
  // app.use(createUpstreamProxy());

  return app;
}

const app = createApp();

module.exports = app;
module.exports.createApp = createApp;
