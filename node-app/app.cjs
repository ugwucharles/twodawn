require('./config/envLoader.cjs');
const express = require('express');
const { attachRequestContext } = require('./middleware/requestContext.cjs');
const { registerRoutes } = require('./routes/index.cjs');
const { createUpstreamProxy } = require('./services/upstreamProxy.cjs');
const { ensureUsersSchema } = require('./db/ensureUsersSchema.cjs');
const { ensureOrdersSchema } = require('./db/ensureOrdersSchema.cjs');
const { ensureEventsSchema } = require('./db/ensureEventsSchema.cjs');

ensureUsersSchema().catch((error) => {
  console.error('Failed to ensure users schema:', error.message);
});

ensureOrdersSchema().catch((error) => {
  console.error('Failed to ensure orders schema:', error.message);
});

ensureEventsSchema().catch((error) => {
  console.error('Failed to ensure events schema:', error.message);
});

function createApp() {
  const app = express();

  app.disable('x-powered-by');
  app.set('trust proxy', true);

  app.use(attachRequestContext);

  app.use(express.json({ limit: '1mb' }));
  app.use(express.urlencoded({ extended: false }));

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

  // Legacy Laravel proxy support has been removed. The app now runs entirely on the Node stack.
  // app.use(createUpstreamProxy());

  return app;
}

const app = createApp();

module.exports = app;
module.exports.createApp = createApp;
