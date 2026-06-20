const express = require('express');
const { pingDatabase } = require('../db/client.cjs');

function createSystemRouter() {
  const router = express.Router();

  // Keep existing contract: { ok, time }.
  router.get('/health', (_req, res) => {
    return res.status(200).json({
      ok: true,
      time: new Date().toISOString(),
    });
  });

  // Node-only health endpoint for migration visibility.
  router.get('/_node/health', (_req, res) => {
    return res.status(200).json({
      ok: true,
      service: 'node-express',
      mode: 'hybrid-proxy',
      time: new Date().toISOString(),
    });
  });

  // Internal migration check for Node <-> MySQL connectivity.
  router.get('/_node/db/health', async (_req, res) => {
    const db = await pingDatabase();
    const statusCode = db.ok ? 200 : db.status === 'not_configured' ? 200 : 503;

    return res.status(statusCode).json({
      ok: db.ok,
      status: db.status,
      configured: db.configured,
      config: db.config,
      error: db.error || null,
      time: new Date().toISOString(),
    });
  });

  return router;
}

module.exports = {
  createSystemRouter,
};
