require('./config/envLoader.cjs');
const express = require('express');
const { attachRequestContext } = require('./middleware/requestContext.cjs');
const { registerRoutes } = require('./routes/index.cjs');
const { createUpstreamProxy } = require('./services/upstreamProxy.cjs');
const { ensureUsersSchema } = require('./db/ensureUsersSchema.cjs');
const { ensureOrdersSchema } = require('./db/ensureOrdersSchema.cjs');
const { ensureEventsSchema } = require('./db/ensureEventsSchema.cjs');
const { ensureActivityLogsSchema } = require('./db/ensureActivityLogsSchema.cjs');
const { ensureWithdrawalsSchema } = require('./db/ensureWithdrawalsSchema.cjs');

async function initializeSchemas() {
  try {
    await ensureUsersSchema();
    console.log('✅ Users schema ensured');
  } catch (error) {
    console.error('Failed to ensure users schema:', error.message);
  }

  try {
    await ensureOrdersSchema();
    console.log('✅ Orders schema ensured');
  } catch (error) {
    console.error('Failed to ensure orders schema:', error.message);
  }

  try {
    await ensureEventsSchema();
    console.log('✅ Events schema ensured');
  } catch (error) {
    console.error('Failed to ensure events schema:', error.message);
  }

  try {
    await ensureActivityLogsSchema();
    console.log('✅ Activity logs schema ensured');
  } catch (error) {
    console.error('Failed to ensure activity logs schema:', error.message);
  }

  try {
    await ensureWithdrawalsSchema();
    console.log('✅ Withdrawals schema ensured');
  } catch (error) {
    console.error('Failed to ensure withdrawals schema:', error.message);
  }
}

// Delay schema initialization slightly to prevent startup CPU spikes from causing Connect Timeout errors
setTimeout(initializeSchemas, 2000);

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
