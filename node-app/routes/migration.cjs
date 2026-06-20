const express = require('express');
const { createAuthRouter, createPublicAuthRouter } = require('./auth.cjs');

function createMigrationRouter() {
  const router = express.Router();

  // Step 4: public Laravel auth routes (POST/PUT/PATCH/DELETE handled in Node; GET pages proxy to Laravel).
  router.use(createPublicAuthRouter());

  // Step 4 migration namespace for explicit Node auth testing and API clients.
  router.use('/_node/auth', createAuthRouter());

  // These route groups are the ownership boundaries for incremental migration.
  // Handlers will be added step-by-step in later migration phases.
  router.use('/api/v1', express.Router());
  router.use('/events', express.Router());
  router.use('/event', express.Router());
  router.use('/admin', express.Router());
  router.use('/organizer', express.Router());
  router.use('/h', express.Router());

  return router;
}

module.exports = {
  createMigrationRouter,
};
